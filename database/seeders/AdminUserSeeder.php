<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('lonjoboko.admin.email');

        User::firstOrCreate(
            ['email' => $email],
            [
                'name' => config('lonjoboko.admin.name'),
                'password' => config('lonjoboko.admin.password'),
                'role' => UserRole::Admin,
                'is_active' => true,
            ],
        );

        $this->command?->warn("Akun admin: {$email}. Segera ganti kata sandinya setelah login pertama.");
    }
}
