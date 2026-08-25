<?php

namespace App\Http\Middleware;

use App\Enums\AccountType;
use App\Models\AdministratorModel;
use App\Models\UbsModel;
use App\Models\UserModel;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountType
{
    public function handle(Request $request, Closure $next, string ...$allowedTypes): Response
    {
        $account = $request->user();
        $accountType = match (true) {
            $account instanceof UbsModel => AccountType::Ubs,
            $account instanceof UserModel => AccountType::User,
            $account instanceof AdministratorModel => AccountType::Administrator,
            default => null,
        };

        if ($accountType === null || ! in_array($accountType->value, $allowedTypes, true)) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $unitIsActive = ! $account instanceof UserModel || $account->hasActiveAccountContext();

        if (! $account->is_active || ! $unitIsActive) {
            if (! $request->expectsJson()) {
                Auth::guard($accountType->guard())->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()
                    ->route(match ($accountType) {
                        AccountType::Ubs => 'ubs.login',
                        AccountType::User => 'user.login',
                        AccountType::Administrator => 'admin.login',
                    })
                    ->withErrors(['identifier' => 'Esta conta está inativa.']);
            }

            abort(Response::HTTP_FORBIDDEN);
        }

        if ($account->currentAccessToken() !== null && ! $account->tokenCan($accountType->ability())) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
