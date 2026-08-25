<?php

namespace App\Providers;

use App\Models\AssessmentModel;
use App\Models\AuditEventModel;
use App\Models\DistrictModel;
use App\Models\PatientModel;
use App\Models\QuestionnaireVersionModel;
use App\Models\ReportModel;
use App\Models\RiskModel;
use App\Models\UbsModel;
use App\Models\UserModel;
use App\Policies\AssessmentPolicies\AssessmentPolicy;
use App\Policies\AuditEventPolicies\AuditEventPolicy;
use App\Policies\DistrictPolicies\DistrictPolicy;
use App\Policies\PatientPolicies\PatientPolicy;
use App\Policies\QuestionnairePolicies\QuestionnaireVersionPolicy;
use App\Policies\ReportPolicies\ReportPolicy;
use App\Policies\RiskPolicies\RiskPolicy;
use App\Policies\UbsPolicies\UbsPolicy;
use App\Policies\UserPolicies\UserPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Password::defaults(fn (): Password => Password::min(12)
            ->mixedCase()
            ->numbers()
            ->symbols());

        Paginator::useBootstrapFive();

        RateLimiter::for('login', function (Request $request): Limit {
            $key = implode('|', [
                Str::lower(trim((string) $request->input('account_type'))),
                Str::lower(trim((string) $request->input('identifier'))),
                (string) $request->ip(),
            ]);

            return Limit::perMinute(5)->by($key);
        });

        RateLimiter::for('ubs-registration', function (Request $request): array {
            $cnes = trim((string) $request->input('cnes'));
            $ip = (string) $request->ip();

            return [
                Limit::perHour(5)->by("ubs-registration|ip|{$ip}"),
                Limit::perDay(3)->by("ubs-registration|cnes|{$cnes}|{$ip}"),
            ];
        });

        Gate::policy(AssessmentModel::class, AssessmentPolicy::class);
        Gate::policy(AuditEventModel::class, AuditEventPolicy::class);
        Gate::policy(DistrictModel::class, DistrictPolicy::class);
        Gate::policy(PatientModel::class, PatientPolicy::class);
        Gate::policy(ReportModel::class, ReportPolicy::class);
        Gate::policy(RiskModel::class, RiskPolicy::class);
        Gate::policy(QuestionnaireVersionModel::class, QuestionnaireVersionPolicy::class);
        Gate::policy(UbsModel::class, UbsPolicy::class);
        Gate::policy(UserModel::class, UserPolicy::class);

        // Registra que as migrations dentro de ./migrations/* devem ser rodadas

        $mainPath = database_path('migrations');
        $directories = glob($mainPath.'/*', GLOB_ONLYDIR);

        $this->loadMigrationsFrom(array_merge([$mainPath], $directories));
    }
}
