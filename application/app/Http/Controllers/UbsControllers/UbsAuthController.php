<?php

namespace App\Http\Controllers\UbsControllers;

use App\Http\Controllers\Controller;
use App\Services\UbsServices\KeycloakUbsAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Throwable;

class UbsAuthController extends Controller
{
    public function __construct(
        protected KeycloakUbsAuthService $authService,
    ) {
    }

    public function redirect(): RedirectResponse
    {
        return Socialite::driver('keycloak')
            ->scopes(['openid', 'profile', 'email'])
            ->stateless()
            ->redirect();
    }

    public function callback(): JsonResponse
    {
        try {
            $keycloakUser = Socialite::driver('keycloak')
                ->stateless()
                ->user();
        } catch (Throwable) {
            return response()->json([
                'message' => 'Nao foi possivel autenticar a UBS pelo Keycloak.',
            ], 401);
        }

        $ubs = $this->authService->findUbsFromSocialiteUser($keycloakUser);

        if ($ubs === null) {
            return response()->json([
                'message' => 'A conta Keycloak autenticada nao esta vinculada a uma UBS ativa.',
            ], 403);
        }

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $keycloakUser->token,
            'refresh_token' => $keycloakUser->refreshToken,
            'expires_in' => $keycloakUser->expiresIn,
            'ubs' => $ubs,
        ]);
    }

    public function me(): JsonResponse
    {
        $ubs = Auth::guard('keycloak')->user();
        Gate::authorize('view', $ubs);

        return response()->json($ubs);
    }

    public function logout(): JsonResponse
    {
        Gate::authorize('view', Auth::guard('keycloak')->user());

        return response()->json([
            'logout_url' => Socialite::driver('keycloak')->getLogoutUrl(
                config('app.url'),
                config('services.keycloak.client_id'),
            ),
        ]);
    }
}
