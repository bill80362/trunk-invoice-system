<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

#[Signature('app:setup-admin')]
#[Description('建立或更新預設管理員帳號密碼')]
class SetupAdminCommand extends Command
{
    public function handle(): int
    {
        $name = text(
            label: '管理員名稱',
            default: 'Admin',
            required: true,
        );

        $email = text(
            label: '管理員 Email',
            default: 'admin@admin.com',
            required: true,
            validate: ['email' => 'required|email'],
        );

        $password = password(
            label: '管理員密碼',
            required: true,
            validate: ['password' => 'required|min:1'],
        );

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => $password,
            ],
        );

        $this->info("管理員帳號已設定：{$user->email}");

        return self::SUCCESS;
    }
}
