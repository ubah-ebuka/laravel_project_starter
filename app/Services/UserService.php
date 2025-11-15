<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use App\DTOs\UserDTO;
use App\Enums\RoleEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\json;

class UserService {
    public function __construct()
    {
        
    }

    public function createUser(UserDTO $userDTO): void
    {
        DB::beginTransaction();
        try {
            $user = new User();
            $user->first_name = strtolower($userDTO->first_name);
            $user->last_name = strtolower($userDTO->last_name);
            $user->email = $userDTO->email;
            $user->phone = $userDTO->phone;
            $user->type = $userDTO->type;
            $user->currency_code = $userDTO->currency_code->value;
            $user->password = Hash::make($userDTO->password);

            // Assign default role or permissions if needed
            if ($userDTO->type === 'customer') {
                $user->role_id = Role::where(['name' => RoleEnum::CUSTOMER->value, 'type' => 'customer'])->first()->id;
            } elseif ($userDTO->type === 'admin') {
                $user->role_id = Role::where(['name' => RoleEnum::ADMIN->value, 'type' => 'admin'])->first()->id;
            }

            $user->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User creation failed', [
                'error' => $e->getMessage(),
                'user_data' => $userDTO
            ]);
            abort(500, 'User creation failed. Please try again later.');
        }
    }

    public function resetPassword(User $user, string $newPassword): void
    {
        $user->password = Hash::make($newPassword);
        $user->save();
    }

    public function verifyEmail(User $user): void
    {
        $user->email_verified_at = now();
        $user->save();
    }

    public function verifyPhoneNumber(User $user): void
    {
        $user->phone_verified_at = now();
        $user->save();
    }

    public function isUserActive(User $user): bool
    {
        return $user->status === 'active';
    }
}