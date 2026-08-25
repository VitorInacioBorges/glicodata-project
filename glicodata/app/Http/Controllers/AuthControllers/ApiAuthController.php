<?php

namespace App\Http\Controllers\AuthControllers;

use App\Enums\AccountType;
use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequests\ChangePasswordRequest;
use App\Http\Requests\AuthRequests\LoginRequest;
use App\Models\AdministratorModel;
use App\Models\UbsModel;
use App\Models\UserModel;
use App\Services\AuthServices\AuthenticationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ApiAuthController extends Controller
{
    public function __construct(
        protected AuthenticationService $authenticationService,
    ) {}

    public function login(LoginRequest $request): JsonResponse
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

        $issued = $this->authenticationService->issueToken($account, $accountType, $data['device_name']);

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $issued['token']->plainTextToken,
            'expires_at' => $issued['expires_at']->toISOString(),
            'account_type' => $accountType->value,
            'identity' => $this->authenticationService->identity($account),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $account = $this->authenticatedAccount($request);

        return response()->json([
            'account_type' => $this->accountType($account)->value,
            'identity' => $this->authenticationService->identity($account),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $account = $this->authenticatedAccount($request);
        $account->currentAccessToken()?->delete();

        return response()->json(null, 204);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $account = $this->authenticatedAccount($request);

        if (! Hash::check((string) $request->validated('current_password'), $account->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['A senha atual está incorreta.'],
            ]);
        }

        $this->authenticationService->replacePassword($account, (string) $request->validated('password'));

        return response()->json(null, 204);
    }

    private function authenticatedAccount(Request $request): UbsModel|UserModel|AdministratorModel
    {
        $account = $request->user();

        abort_unless($account instanceof UbsModel || $account instanceof UserModel || $account instanceof AdministratorModel, 403);

        return $account;
    }

    private function accountType(UbsModel|UserModel|AdministratorModel $account): AccountType
    {
        return match (true) {
            $account instanceof UbsModel => AccountType::Ubs,
            $account instanceof UserModel => AccountType::User,
            default => AccountType::Administrator,
        };
    }
}
