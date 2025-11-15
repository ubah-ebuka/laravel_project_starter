<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\DTOs\UserDTO;
use App\Enums\RoleEnum;
use App\Services\UserService;
use App\Enums\CurrencyCodeEnum;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment('local', 'staging', 'development')) {
            $existingSuperAdmin = User::where(['type' => 'admin'])
                ->whereHas('role', function ($query) {
                    $query->where('identifier', RoleEnum::SUPER_ADMIN->value);
                })->first();

            if (empty($existingSuperAdmin)) {
                $userDTO = new UserDTO(
                    first_name: 'Admin',
                    last_name: 'Admin',
                    email: 'admin@admin.com',
                    phone: '08011111111',
                    password: env('SPPD'),
                    type: 'admin',
                    status: 'active',
                    currency_code: CurrencyCodeEnum::NGN
                );
        
                $userService = app(UserService::class);
                $userService->createUser($userDTO);
        
                $user = User::where(['email' => $userDTO->email, 'type' => $userDTO->type])->first();
                $user->role_id = Role::where(['identifier' => RoleEnum::SUPER_ADMIN->value, 'type' => 'admin'])->first()->id;
                $user->save();
            }
        }
    }
}
