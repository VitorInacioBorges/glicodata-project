<?php

namespace App\Services\AuditEventServices;

use App\Models\AdministratorModel;
use App\Models\AuditEventModel;
use App\Models\UbsModel;
use App\Models\UserModel;
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

    public function __construct(
        protected AuditEventRepository $repository,
    ) {}

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
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     */
    public function record(
        string $action,
        Model $subject,
        string $ownerUbsId,
        ?array $before,
        ?array $after,
        UbsModel|UserModel|AdministratorModel|null $actor = null,
    ): AuditEventModel {
        $actor ??= Auth::user();

        if (! $actor instanceof UbsModel && ! $actor instanceof UserModel && ! $actor instanceof AdministratorModel) {
            throw new LogicException('Uma conta autenticada e obrigatória para registrar auditoria.');
        }

        return $this->repository->createAuditEvent([
            'actor_ubs_id' => $actor instanceof UbsModel ? $actor->id : null,
            'actor_administrator_id' => $actor instanceof AdministratorModel ? $actor->id : null,
            'actor_user_id' => $actor instanceof UserModel ? $actor->id : null,
            'owner_ubs_id' => $ownerUbsId,
            'actor_name' => $actor instanceof UbsModel
                ? ($actor->name ?: "UBS CNES {$actor->cnes}")
                : $actor->name,
            'actor_email' => $actor->email,
            'subject_type' => $subject->getTable(),
            'subject_id' => $subject->getKey(),
            'action' => $action,
            'before_payload' => $before,
            'after_payload' => $after,
        ]);
    }

    public function redactAuditEvent(string $id, string $reason, AdministratorModel $actor): AuditEventModel
    {
        return DB::transaction(function () use ($id, $reason, $actor): AuditEventModel {
            $event = $this->getAuditEventById($id);
            $before = [
                'id' => $event->id,
                'action' => $event->action,
                'redacted_at' => $event->redacted_at,
            ];

            $event->fill([
                'before_payload' => null,
                'after_payload' => null,
                'redacted_at' => now(),
                'redacted_by_ubs_id' => null,
                'redacted_by_administrator_id' => $actor->id,
                'redaction_reason' => $reason,
            ])->save();

            $event = $event->refresh();

            $this->record(
                'redact',
                $event,
                (string) $event->owner_ubs_id,
                $before,
                [
                    'id' => $event->id,
                    'action' => $event->action,
                    'redacted_at' => $event->redacted_at?->toISOString(),
                ],
                $actor,
            );

            return $event;
        });
    }

    private function normalizePerPage(int $perPage): int
    {
        return max(1, min(20, $perPage));
    }
}
