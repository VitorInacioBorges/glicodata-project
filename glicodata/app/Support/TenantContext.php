<?php

namespace App\Support;

use App\Models\UbsModel;
use App\Models\UserModel;
use Illuminate\Contracts\Auth\Authenticatable;

class TenantContext
{
    public function ubsId(Authenticatable $account): string
    {
        return match (true) {
            $account instanceof UbsModel => (string) $account->id,
            $account instanceof UserModel => (string) $account->ubs_id,
            default => abort(403),
        };
    }

    public function user(Authenticatable $account): UserModel
    {
        abort_unless($account instanceof UserModel, 403);

        return $account;
    }
}
