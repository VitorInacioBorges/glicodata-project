<?php

namespace App\Services\ReportServices;

use App\Enums\AssessmentStatus;
use App\Models\AssessmentModel;
use App\Models\ReportModel;
use App\Repositories\ReportRepositories\ReportRepository;
use App\Services\AuditEventServices\AuditEventService;
use App\Utils\ValidateUtils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReportService
{
    use ValidateUtils;

    public function __construct(
        protected ReportRepository $repository,
        protected AuditEventService $auditService,
    ) {}

    public function getReportsForUbs(int $perPage, string $ubsId): LengthAwarePaginator
    {
        $this->validateId($ubsId);

        return $this->repository->paginateReportsForUbs($this->normalizePerPage($perPage), $ubsId);
    }

    public function getReportById(string $id): ReportModel
    {
        $this->validateId($id);

        $report = $this->repository->findReportById($id);

        if ($report === null) {
            throw (new ModelNotFoundException)->setModel(ReportModel::class, [$id]);
        }

        return $report;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createReport(array $data): ReportModel
    {
        $assessment = AssessmentModel::query()->findOrFail((string) $data['assessment_id']);
        if ($assessment->status !== AssessmentStatus::Completed) {
            throw ValidationException::withMessages([
                'assessment_id' => ['Relatórios somente podem ser criados para anamneses concluídas.'],
            ]);
        }

        return DB::transaction(function () use ($data): ReportModel {
            $report = $this->repository->createReport($data);
            $this->auditService->record('create', $report, $this->ownerUbsId($report), null, $report->toArray());

            return $report;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateReport(string $id, array $data): ReportModel
    {
        $report = $this->getReportById($id);

        return DB::transaction(function () use ($report, $data): ReportModel {
            $before = $report->toArray();
            $report->fill($data)->save();
            $report = $report->refresh();

            $this->auditService->record('update', $report, $this->ownerUbsId($report), $before, $report->toArray());

            return $report;
        });
    }

    public function deleteReport(string $id): bool
    {
        return $this->deleteReportInstance($this->getReportById($id));
    }

    public function deleteReportInstance(ReportModel $report): bool
    {
        return DB::transaction(function () use ($report): bool {
            $before = $report->toArray();
            $deleted = (bool) $report->delete();

            if ($deleted) {
                $this->auditService->record('delete', $report, $this->ownerUbsId($report), $before, $report->toArray());
            }

            return $deleted;
        });
    }

    private function normalizePerPage(int $perPage): int
    {
        return max(1, min(20, $perPage));
    }

    private function ownerUbsId(ReportModel $report): string
    {
        return (string) $report->assessment()->value('ubs_id');
    }

    /**
     * A exportação é propositalmente agregada: não contém identificadores,
     * datas individuais, respostas, sintomas nem texto livre dos relatórios.
     *
     * @return array<int, array{questionnaire: string, version: int, classification: string, total: int|null, suppressed: bool}>
     */
    public function getAnonymizedSummaryForUbs(string $ubsId): array
    {
        $this->validateId($ubsId);

        return DB::table('reports')
            ->join('assessments', 'assessments.id', '=', 'reports.assessment_id')
            ->join('risks', 'risks.assessment_id', '=', 'assessments.id')
            ->join('questionnaire_versions', 'questionnaire_versions.id', '=', 'assessments.questionnaire_version_id')
            ->join('questionnaires', 'questionnaires.id', '=', 'questionnaire_versions.questionnaire_id')
            ->where('assessments.ubs_id', $ubsId)
            ->whereNull('reports.deleted_at')
            ->whereNull('assessments.deleted_at')
            ->whereNull('risks.deleted_at')
            ->groupBy('questionnaires.code', 'questionnaire_versions.version', 'risks.classification')
            ->orderBy('questionnaires.code')
            ->orderBy('questionnaire_versions.version')
            ->orderBy('risks.classification')
            ->selectRaw('questionnaires.code as questionnaire, questionnaire_versions.version, risks.classification, COUNT(*) as total')
            ->get()
            ->map(static function (object $row): array {
                $total = (int) $row->total;

                return [
                    'questionnaire' => (string) $row->questionnaire,
                    'version' => (int) $row->version,
                    'classification' => (string) $row->classification,
                    'total' => $total >= 5 ? $total : null,
                    'suppressed' => $total < 5,
                ];
            })
            ->all();
    }

    /**
     * @param  array<int, array{questionnaire: string, version: int, classification: string, total: int|null, suppressed: bool}>  $summary
     */
    public function toAnonymizedCsv(array $summary): string
    {
        $stream = fopen('php://temp', 'r+');
        abort_if($stream === false, 500);

        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, ['questionario', 'versao', 'classificacao', 'total', 'suprimido'], ';');
        foreach ($summary as $row) {
            fputcsv($stream, [
                $row['questionnaire'],
                $row['version'],
                $row['classification'],
                $row['total'] ?? '<5',
                $row['suppressed'] ? 'sim' : 'nao',
            ], ';');
        }
        rewind($stream);
        $contents = stream_get_contents($stream);
        fclose($stream);

        return (string) $contents;
    }
}
