<?php

namespace App\Console\Commands;

use App\Models\AdministratorModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class CreateAdministratorCommand extends Command
{
    protected $signature = 'glicodata:admin-create {admin_code?}';

    protected $description = 'Cria uma conta administrativa global por código e senha';

    public function handle(): int
    {
        $adminCode = Str::upper(trim((string) ($this->argument('admin_code') ?: $this->ask('Código do administrador'))));
        $password = (string) $this->secret('Senha');
        $confirmation = (string) $this->secret('Confirme a senha');

        $validator = Validator::make([
            'admin_code' => $adminCode,
            'password' => $password,
            'password_confirmation' => $confirmation,
        ], [
            'admin_code' => ['required', 'string', 'max:40', 'regex:/^[A-Z0-9_-]+$/', 'unique:administrators,admin_code'],
            'password' => ['required', 'string', 'max:255', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        AdministratorModel::query()->create([
            'admin_code' => $adminCode,
            'password' => $password,
            'is_active' => true,
        ]);

        $this->info('Administrador criado com sucesso.');

        return self::SUCCESS;
    }
}
