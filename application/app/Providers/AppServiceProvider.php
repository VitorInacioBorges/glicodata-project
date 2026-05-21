<?php

namespace App\Providers;

use App\Models\AssessmentModel;
use App\Models\DistrictModel;
use App\Models\PatientModel;
use App\Models\ReportModel;
use App\Models\RiskModel;
use App\Models\UbsModel;
use App\Models\UserModel;
use App\Policies\AssessmentPolicies\AssessmentPolicy;
use App\Policies\DistrictPolicies\DistrictPolicy;
use App\Policies\PatientPolicies\PatientPolicy;
use App\Policies\ReportPolicies\ReportPolicy;
use App\Policies\RiskPolicies\RiskPolicy;
use App\Policies\UbsPolicies\UbsPolicy;
use App\Policies\UserPolicies\UserPolicy;
use App\Services\UbsServices\KeycloakUbsAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use SocialiteProviders\Keycloak\Provider as KeycloakProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

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
        Event::listen(function (SocialiteWasCalled $event): void {
            $event->extendSocialite('keycloak', KeycloakProvider::class);
        });

        Auth::viaRequest('keycloak', function (Request $request): ?UbsModel {
            return app(KeycloakUbsAuthService::class)
                ->findUbsFromBearerToken($request->bearerToken());
        });

        Gate::policy(AssessmentModel::class, AssessmentPolicy::class);
        Gate::policy(DistrictModel::class, DistrictPolicy::class);
        Gate::policy(PatientModel::class, PatientPolicy::class);
        Gate::policy(ReportModel::class, ReportPolicy::class);
        Gate::policy(RiskModel::class, RiskPolicy::class);
        Gate::policy(UbsModel::class, UbsPolicy::class);
        Gate::policy(UserModel::class, UserPolicy::class);

        // Registra que as migrations dentro de ./migrations/* devem ser rodadas

        $mainPath = database_path('migrations');
        $directories = glob($mainPath . '/*', GLOB_ONLYDIR);

        $this->loadMigrationsFrom(array_merge([$mainPath], $directories));
    }
}
