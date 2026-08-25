<?php

namespace App\Console\Commands;

use App\Models\AdministratorModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class CreateAdministratorCommand extends Command
{
    protected $signature = 'glicodata:admin-create {--name=} {--email=}';

    protected $description = 'Cria uma conta administrativa global do GlicoData';

    public function handle(): int
    {
        $name = trim((string) ($this->option('name') ?: $this->ask('Nome')));
        $email = Str::lower(trim((string) ($this->option('email') ?: $this->ask('E-mail'))));
        $password = (string) $this->secret('Senha');
        $confirmation = (string) $this->secret('Confirme a senha');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $confirmation,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255', 'unique:administrators,email'],
            'password' => ['required', 'string', 'max:255', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        AdministratorModel::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'is_active' => true,
        ]);

        $this->info('Administrador criado com sucesso.');

        return self::SUCCESS;
    }
}
