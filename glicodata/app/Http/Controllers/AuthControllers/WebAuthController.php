<?php

namespace App\Http\Controllers\AuthControllers;

use App\Enums\AccountType;
use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequests\ChangePasswordRequest;
use App\Http\Requests\AuthRequests\WebLoginRequest;
use App\Models\AdministratorModel;
use App\Models\UbsModel;
use App\Models\UserModel;
use App\Services\AuthServices\AuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class WebAuthController extends Controller
{
    public function __construct(
        protected AuthenticationService $authenticationService,
    ) {}

    public function login(WebLoginRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $accountType = AccountType::from($data['account_type']);
        $account = $this->authenticationService->authenticate(
            $accountType,
            $data['identifier'],
            $data['password'],
        );

        if (! $account instanceof UbsModel && ! $account instanceof UserModel && ! $account instanceof AdministratorModel) {
            throw ValidationException::withMessages([
                'identifier' => ['As credenciais informadas são inválidas.'],
            ]);
        }

        foreach (['ubs', 'user', 'admin'] as $guard) {
            if ($guard !== $accountType->guard()) {
                Auth::guard($guard)->logout();
            }
        }
        Auth::guard($accountType->guard())->login($account);
        $request->session()->regenerate();

        return redirect()->intended(
            match ($accountType) {
                AccountType::Ubs => route('ubs.lobby'),
                AccountType::User => route('ubs.patients.index'),
                AccountType::Administrator => route('admin.dashboard'),
            },
        );
    }

    public function logout(Request $request, string $accountType): RedirectResponse
    {
        $type = AccountType::from($accountType);
        Auth::guard($type->guard())->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route(match ($type) {
            AccountType::Ubs => 'ubs.login',
            AccountType::User => 'user.login',
            AccountType::Administrator => 'admin.login',
        });
    }

    public function changePassword(ChangePasswordRequest $request, string $accountType): RedirectResponse
    {
        $type = AccountType::from($accountType);
        $account = $request->user();

        abort_unless($account instanceof UbsModel || $account instanceof UserModel || $account instanceof AdministratorModel, 403);

        if (! Hash::check((string) $request->validated('current_password'), $account->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['A senha atual está incorreta.'],
            ]);
        }

        $this->authenticationService->replacePassword($account, (string) $request->validated('password'));
        Auth::guard($type->guard())->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route(match ($type) {
                AccountType::Ubs => 'ubs.login',
                AccountType::User => 'user.login',
                AccountType::Administrator => 'admin.login',
            })
            ->with('status', 'Senha atualizada. Entre novamente.');
    }
}
