<?php

namespace App\Support;

use App\Models\UbsModel;
use Illuminate\Contracts\Auth\Authenticatable;

class TenantContext
{
    public function ubsId(Authenticatable $account): string
    {
        abort_unless($account instanceof UbsModel, 403);

        return (string) $account->id;
    }
}
