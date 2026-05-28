<?php

namespace App\Http\Controllers\UbsControllers;

use App\Http\Controllers\Controller;
use App\Services\UbsServices\KeycloakUbsAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    public function redirect(): JsonResponse|RedirectResponse
    {
        if ((bool) config('glicodata.auth_disabled')) {
            return response()->json([
                'message' => 'Autenticacao institucional desativada temporariamente neste ambiente.',
                'auth_disabled' => true,
            ]);
        }

        if (! $this->hasKeycloakProviderConfiguration()) {
            return response()->json([
                'message' => 'O acesso institucional ainda nao foi configurado neste ambiente.',
            ], 503);
        }

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

    public function webRedirect(): RedirectResponse
    {
        if ((bool) config('glicodata.auth_disabled')) {
            return redirect()->route('ubs.lobby');
        }

        if (! $this->hasKeycloakProviderConfiguration() || trim($this->webRedirectUri()) === '') {
            return redirect()
                ->route('login')
                ->with('auth_error', 'O acesso institucional ainda nao foi configurado neste ambiente.');
        }

        return Socialite::driver('keycloak')
            ->redirectUrl($this->webRedirectUri())
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function webCallback(Request $request): RedirectResponse
    {
        try {
            $keycloakUser = Socialite::driver('keycloak')
                ->redirectUrl($this->webRedirectUri())
                ->user();
        } catch (Throwable) {
            return redirect()
                ->route('login')
                ->with('auth_error', 'Nao foi possivel autenticar a UBS pelo acesso institucional.');
        }

        $ubs = $this->authService->findUbsFromSocialiteUser($keycloakUser);

        if ($ubs === null) {
            return redirect()
                ->route('login')
                ->with('auth_error', 'A conta autenticada nao esta vinculada a uma UBS ativa.');
        }

        Auth::guard('ubs')->login($ubs);
        $request->session()->regenerate();

        return redirect()->intended(route('ubs.lobby'));
    }

    public function webLogout(Request $request): RedirectResponse
    {
        Auth::guard('ubs')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if (! $this->hasKeycloakProviderConfiguration()) {
            return redirect()->route('login');
        }

        return redirect()->away(Socialite::driver('keycloak')->getLogoutUrl(
            route('login'),
            config('services.keycloak.client_id'),
        ));
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

        if ((bool) config('glicodata.auth_disabled') || ! $this->hasKeycloakProviderConfiguration()) {
            return response()->json([
                'message' => 'Logout institucional indisponivel enquanto a autenticacao esta desativada.',
            ]);
        }

        return response()->json([
            'logout_url' => Socialite::driver('keycloak')->getLogoutUrl(
                config('app.url'),
                config('services.keycloak.client_id'),
            ),
        ]);
    }

    private function webRedirectUri(): string
    {
        return (string) config('services.keycloak.web_redirect');
    }

    private function hasKeycloakProviderConfiguration(): bool
    {
        foreach (['client_id', 'client_secret', 'base_url', 'realms'] as $key) {
            if (trim((string) config("services.keycloak.{$key}")) === '') {
                return false;
            }
        }

        return true;
    }
}
