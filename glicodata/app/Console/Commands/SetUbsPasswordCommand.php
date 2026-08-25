<?php

namespace App\Console\Commands;

use App\Models\UbsModel;
use App\Services\AuthServices\AuthenticationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class SetUbsPasswordCommand extends Command
{
    protected $signature = 'glicodata:ubs-password {cnes}';

    protected $description = 'Define ou redefine com segurança a senha de uma UBS pelo CNES';

    public function __construct(
        protected AuthenticationService $authenticationService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $cnes = trim((string) $this->argument('cnes'));
        $ubs = UbsModel::query()->where('cnes', $cnes)->first();

        if (! $ubs instanceof UbsModel) {
            $this->error('UBS não encontrada.');

            return self::FAILURE;
        }

        $password = (string) $this->secret('Nova senha');
        $confirmation = (string) $this->secret('Confirme a nova senha');
        $validator = Validator::make([
            'password' => $password,
            'password_confirmation' => $confirmation,
        ], [
            'password' => ['required', 'string', 'max:255', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $this->authenticationService->replacePassword($ubs, $password);

        $this->info('Senha da UBS atualizada e tokens revogados.');

        return self::SUCCESS;
    }
}
