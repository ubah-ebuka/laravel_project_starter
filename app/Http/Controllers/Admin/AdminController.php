<?php 

namespace App\Http\Controllers\Admin;

use App\DTOs\UserDTO;
use App\Enums\CurrencyCodeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AddAdminRequest;
use App\Models\User;
use App\Notifications\GeneralNoty;
use App\Services\UserService;
use App\Services\UtilityService;
use App\Traits\ApiResponse;

class AdminController extends Controller
{
    use ApiResponse;

    public function addAdmin(AddAdminRequest $addAdminRequest, UserService $userService, UtilityService $utilityService)
    {
        $payload = $addAdminRequest->validated();
        $userDTO = new UserDTO(
            first_name: $payload['first_name'],
            last_name: $payload['last_name'],
            email: $payload['email'],
            password: $utilityService->generatePassword(),
            phone: $payload['phone'],
            type: 'admin',
            status: 'active',
            currency_code: CurrencyCodeEnum::NGN
        );

        $userService->createUser($userDTO);
        
        $user = User::where(['email' => $payload['email'], 'type' => 'admin', 'status' => 'active'])->first();
        $user->notify(new GeneralNoty([
            'type' => 'email',
            'recipient' => $user->email,
            'user_id' => $user->id,
            'subject' => 'Welcome to '. config('app.name'),
            'data' => [
                'user' => $user,
                'password' => $userDTO->password
            ],
            'view' => 'admin.welcome'
        ]));
        
        return $this->successResponse(null, 'Admin added successfully.', 200);
    }
}