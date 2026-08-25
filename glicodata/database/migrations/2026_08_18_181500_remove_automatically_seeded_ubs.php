<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            $seededUbsIds = $this->seededUbsIds();
            $seededEmails = DB::table('ubs')
                ->whereIn('id', $seededUbsIds)
                ->whereNotNull('email')
                ->pluck('email');
            $assessmentIds = DB::table('assessments')
                ->whereIn('ubs_id', $seededUbsIds)
                ->pluck('id');

            DB::table('audit_events')
                ->whereIn('owner_ubs_id', $seededUbsIds)
                ->orWhereIn('actor_ubs_id', $seededUbsIds)
                ->orWhereIn('redacted_by_ubs_id', $seededUbsIds)
                ->delete();
            DB::table('personal_access_tokens')
                ->where('tokenable_type', 'App\\Models\\UbsModel')
                ->whereIn('tokenable_id', $seededUbsIds)
                ->delete();
            DB::table('sessions')->whereIn('user_id', $seededUbsIds)->delete();
            DB::table('password_reset_tokens')->whereIn('email', $seededEmails)->delete();
            DB::table('risks')->whereIn('assessment_id', $assessmentIds)->delete();
            DB::table('reports')->whereIn('assessment_id', $assessmentIds)->delete();
            DB::table('assessments')->whereIn('ubs_id', $seededUbsIds)->delete();
            DB::table('patients')->whereIn('ubs_id', $seededUbsIds)->delete();
            DB::table('users')->whereIn('ubs_id', $seededUbsIds)->delete();
            DB::table('ubs')->whereIn('id', $seededUbsIds)->delete();

            $legacyUbsIds = DB::table('ubs')->whereNull('cnes')->pluck('id');

            DB::table('ubs')->whereIn('id', $legacyUbsIds)->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);
            DB::table('personal_access_tokens')
                ->where('tokenable_type', 'App\\Models\\UbsModel')
                ->whereIn('tokenable_id', $legacyUbsIds)
                ->delete();
            DB::table('sessions')->whereIn('user_id', $legacyUbsIds)->delete();
        });
    }

    /**
     * A remoção contém dados clínicos e de auditoria e é intencionalmente
     * irreversível. Restaurar exige o backup feito antes da implantação.
     */
    public function down(): void
    {
        // Sem restauração automática de dados removidos.
    }

    /**
     * @return Collection<int, string>
     */
    private function seededUbsIds(): Collection
    {
        return collect(range(1, 42))->map(
            static fn (int $position): string => sprintf('20000000-0000-4000-8000-%012d', $position),
        );
    }
};
