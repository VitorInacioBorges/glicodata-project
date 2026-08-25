<?php

namespace App\Services\AuditEventServices;

use App\Models\AdministratorModel;
use App\Models\AuditEventModel;
use App\Models\UbsModel;
use App\Repositories\AuditEventRepositories\AuditEventRepository;
use App\Utils\ValidateUtils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use LogicException;

class AuditEventService
{
    use ValidateUtils;

    public function __construct(protected AuditEventRepository $repository) {}

    public function getAuditEventsForActor(int $perPage, UbsModel|AdministratorModel $actor): LengthAwarePaginator
    {
        return $actor instanceof AdministratorModel
            ? $this->repository->paginateAuditEvents($this->normalizePerPage($perPage))
            : $this->repository->paginateAuditEventsForUbs($this->normalizePerPage($perPage), (string) $actor->id);
    }

    public function getAuditEventById(string $id): AuditEventModel
    {
        $this->validateId($id);
        $event = $this->repository->findAuditEventById($id);

        if ($event === null) {
            throw (new ModelNotFoundException)->setModel(AuditEventModel::class, [$id]);
        }

        return $event;
    }

    /**
     * Values are intentionally discarded. Audit records contain only field
     * names so personal and clinical content cannot be copied into snapshots.
     *
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     */
    public function record(
        string $action,
        Model $subject,
        string $ownerUbsId,
        ?array $before = null,
        ?array $after = null,
        UbsModel|AdministratorModel|null $actor = null,
    ): AuditEventModel {
        $actor ??= Auth::user();

        if (! $actor instanceof UbsModel && ! $actor instanceof AdministratorModel) {
            throw new LogicException('Uma UBS ou administrador autenticado é obrigatório para registrar auditoria.');
        }

        $changedFields = collect(array_unique([...array_keys($before ?? []), ...array_keys($after ?? [])]))
            ->reject(fn (string $field): bool => in_array($field, ['password', 'remember_token'], true))
            ->values()
            ->all();

        return $this->repository->createAuditEvent([
            'actor_ubs_id' => $actor instanceof UbsModel ? $actor->id : null,
            'actor_administrator_id' => $actor instanceof AdministratorModel ? $actor->id : null,
            'owner_ubs_id' => $ownerUbsId,
            'subject_type' => $subject->getTable(),
            'subject_id' => $subject->getKey(),
            'action' => $action,
            'changed_fields' => $changedFields === [] ? null : $changedFields,
        ]);
    }

    public function redactAuditEvent(string $id, string $reason, AdministratorModel $actor): AuditEventModel
    {
        return DB::transaction(function () use ($id, $reason, $actor): AuditEventModel {
            $event = $this->getAuditEventById($id);
            $event->fill([
                'changed_fields' => null,
                'redacted_at' => now(),
                'redacted_by_ubs_id' => null,
                'redacted_by_administrator_id' => $actor->id,
                'redaction_reason' => $reason,
            ])->save();

            $this->record('redact', $event, (string) $event->owner_ubs_id, null, ['redacted_at' => true], $actor);

            return $event->refresh();
        });
    }

    private function normalizePerPage(int $perPage): int
    {
        return max(1, min(20, $perPage));
    }
}
