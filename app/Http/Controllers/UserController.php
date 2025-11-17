<?php

namespace App\Http\Controllers;

use App\DTOs\OtpDTO;
use App\Models\User;
use App\DTOs\UserDTO;
use App\Models\Permission;
use App\Traits\ApiResponse;
use App\Services\OtpService;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Enums\CurrencyCodeEnum;
use App\Enums\OtpActionTypeEnum;
use App\Notifications\GeneralNoty;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ChangeAdminRoleRequest;
use App\Http\Requests\Admin\ChangeCustomerRoleRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Customer\UserLoginRequest;
use App\Http\Requests\Admin\UserLoginRequest as AdminUserLoginRequest;
use App\Http\Requests\Admin\MapUserPermissionRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\Customer\UserRegistrationRequest;
use App\Http\Requests\Customer\UserResetPasswordRequest;
use App\Http\Requests\Customer\UserConfirmPasswordResetRequest;
use App\Models\UserPermission;
use App\Traits\PaginationTrait;
use Exception;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use ApiResponse, PaginationTrait;

    public function register(UserRegistrationRequest $userRegistrationRequest, UserService $userService)
    {
        $payload = $userRegistrationRequest->validated();
        $userDTO = new UserDTO(
            first_name: $payload['first_name'],
            last_name: $payload['last_name'],
            email: $payload['email'],
            password: $payload['password'],
            phone: $payload['phone'],
            type: 'customer',
            status: 'active',
            currency_code: CurrencyCodeEnum::NGN
        );

        $userService->createUser($userDTO);
        
        $user = User::where(['email' => $payload['email'], 'type' => 'customer', 'status' => 'active'])->first();
        Auth::guard('customer')->login($user);
        Auth::setUser($user);

        $user->notify(new GeneralNoty([
            'type' => 'email',
            'recipient' => $user->email,
            'user_id' => $user->id,
            'subject' => 'Welcome to '. config('app.name'),
            'data' => [
                'user' => $user
            ],
            'view' => 'customer.welcome'
        ]));
        
        return $this->user(message: "Registered successfully.");
    }

    public function login(UserLoginRequest $userLoginRequest)
    {
        $payload = $userLoginRequest->validated();
        $user = $userLoginRequest->getValidatedUser();
        Auth::guard('customer')->login($user);
        Auth::setUser($user);
        
        return $this->user(message: "Logged in successfully.");
    }

    public function adminLogin(AdminUserLoginRequest $userLoginRequest)
    {
        $payload = $userLoginRequest->validated();
        $user = $userLoginRequest->getValidatedUser();
        Auth::guard('admin')->login($user);
        Auth::setUser($user);
        
        return $this->user(message: "Logged in successfully.");
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();

        return $this->successResponse(null, 'User logged out successfully.', 200);
    }

    public function adminLogout(Request $request)
    {
        Auth::guard('admin')->logout();

        return $this->successResponse(null, 'User logged out successfully.', 200);
    }

    public function user($message = "User profile retrieved successfully.")
    {
        $user = request()->user();
        $user->attachPermissions();
        return $this->successResponse(new UserResource($user), $message, 200);
    }

    public function passwordReset(UserResetPasswordRequest $request, OtpService $otpService)
    {
        $payload = $request->validated();
        $user = $request->getValidatedUser();

        $token = $otpService->generateOtp(new OtpDTO(
            recipient: $user->email,
            channel: 'email',
            actionType: OtpActionTypeEnum::RESET_PASSWORD->value,
            userID: $user->id
        ));

        $signedUrl = URL::temporarySignedRoute('web-api.password.verify', now()->addMinutes(10), ['email' => $user->email, 'token' => $token]);
        $queryString = parse_url($signedUrl);
        $resetUrl = config("app.frontend_url").'/password/verify'.(isset($queryString['query']) ? '?'.$queryString['query'] : '');

        $user->notify(new GeneralNoty([
            'type' => 'email',
            'recipient' => $user->email,
            'user_id' => $user->id,
            'subject' => 'Password Reset OTP',
            'data' => [
                'resetUrl' => $resetUrl,
                'user' => $user
            ],
            'view' => 'customer.reset-password'
        ]));

        return $this->successResponse(null, "Password reset OTP sent to your email.", 200);
    }

    public function passwordConfirmReset(UserConfirmPasswordResetRequest $request, UserService $userService, OtpService $otpService)
    {
        $payload = $request->validated();
        $user = $request->getValidatedUser();

        $userService->resetPassword($user, $payload['password']);

        $otpService->invalidateOtps(recipient: $user->email, actionType: OtpActionTypeEnum::RESET_PASSWORD->value);

        return $this->successResponse(null, "Password has been reset successfully.", 200);
    }

    public function sendEmailVerificationOtp(OtpService $otpService, UserService $userService)
    {
        $user = request()->user();

        if (!empty($user->email_verified_at)) {
            return $this->failedResponse('Email is already verified.', 400);
        }

        if ($userService->isUserActive($user) === false) {
            return $this->failedResponse('User not in active state.', 400);
        }

        $token = $otpService->generateOtp(new OtpDTO(
            recipient: $user->email,
            channel: 'email',
            actionType: OtpActionTypeEnum::EMAIL_VERIFICATION->value,
            userID: $user->id
        ));

        $signedUrl = URL::temporarySignedRoute('web-api.email.verify', now()->addMinutes(10), ['email' => $user->email, 'token' => $token]);
        $queryString = parse_url($signedUrl);
        $verificationUrl = config("app.frontend_url").'/email/verify'.(isset($queryString['query']) ? '?'.$queryString['query'] : '');

        $user->notify(new GeneralNoty([
            'type' => 'email',
            'recipient' => $user->email,
            'user_id' => $user->id,
            'subject' => 'Verify Your Email Address',
            'data' => [
                'verificationUrl' => $verificationUrl,
                'user' => $user
            ],
            'view' => 'customer.email-verification'
        ]));

        return $this->successResponse(null, "Email vefification OTP sent to your email.", 200);
    }

    public function confirmEmailVerification(Request $request, OtpService $otpService, UserService $userService)
    {
        $user = $request->user();

        if (!empty($user->email_verified_at)) {
            return $this->failedResponse('Email is already verified.', 400);
        }

        $isTokenValid = $otpService->validateOtp(recipient: $user->email, actionType: OtpActionTypeEnum::EMAIL_VERIFICATION->value, token: $request->query('token'));

        if (!$isTokenValid) {
            return $this->failedResponse('Invalid email verification link.', 400);
        }

        $userService->verifyEmail($user);

        $otpService->invalidateOtps(recipient: $user->email, actionType: OtpActionTypeEnum::EMAIL_VERIFICATION->value);

        return $this->successResponse(null, "Email verified successfully.", 200);
    }

    public function sendPhoneNumberOtp(Request $request, OtpService $otpService, NotificationService $notificationService)
    {
        $user = $request->user();

        if ($user->phone_verified_at) {
            return $this->failedResponse('Phone number is already verified.', 400);
        }

        $token = $otpService->generateOtp(new OtpDTO(
            recipient: $user->phone,
            channel: 'sms',
            actionType: OtpActionTypeEnum::PHONE_VERIFICATION->value,
            userID: $user->id
        ));

        $message = "Hi! Use ".$token." to verify your phone number on ".config('app.name').". This code expires in 10 minutes. If you didn't request this, ignore this message.";

        //SMS Service Integration comes here
        $notificationService->sendSms($user->phone, $message, $user);

        return $this->successResponse(null, "Phone number vefification OTP sent.", 200);
    }

    public function confirmPhoneVerification(Request $request, OtpService $otpService, UserService $userService)
    {
        $user = $request->user();

        if ($user->phone_verified_at) {
            return $this->failedResponse('Phone number is already verified.', 400);
        }

        $validator = Validator::make($request->all(), [
            'token' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->failedResponse($validator->errors()->first(), 400);
        }

        $isTokenValid = $otpService->validateOtp(recipient: $user->phone, actionType: OtpActionTypeEnum::PHONE_VERIFICATION->value, token: $request->token);

        if (!$isTokenValid) {
            return $this->failedResponse('Invalid OTP', 400);
        }

        $userService->verifyPhoneNumber($user);

        $otpService->invalidateOtps(recipient: $user->phone, actionType: OtpActionTypeEnum::PHONE_VERIFICATION->value);

        return $this->successResponse(null, "Phone number verified successfully.", 200);
    }

    public function mapPermissions(MapUserPermissionRequest $mapUserPermissionRequest) 
    {
        $mappings = $mapUserPermissionRequest->mappedUserPermission;
        $user = $mapUserPermissionRequest->user;

        DB::beginTransaction();

        try{
            UserPermission::where(['user_id' => $user->id])->delete();
            UserPermission::insert(array_values($mappings));
            DB::commit();

            cache()->forget("compute_user_permissions_{$user->id}");

            return $this->successResponse(null, 'Roles mapped successfully.', 200);
        }catch(Exception $error) {
            DB::rollBack();
            return $this->failedResponse("Roles not mapped", 422);
        }
    }

    public function customers()
    {
        return $this->users('customer');
    }

    public function admins()
    {
        return $this->users('admin');
    }

    public function users(string $type) {
        $users = User::where('type', $type)->orderBy('id', 'desc')->paginate(20);

        return $this->successResponse($this->usePagination(UserResource::collection($users), $users), "Fetched users successfully.", 200);
    }

    public function changePassword(ChangePasswordRequest $changePasswordRequest)
    {
        $user = request()->user();
        $validated = $changePasswordRequest->validated();

        $user->password = Hash::make($validated['new']);
        $user->save();

        return $this->successResponse(null, "Password updated successfully.", 200);
    }

    public function changeRole(User $user, int $roleId)
    {
        $user->role_id = $roleId;
        $user->save();
    }

    public function changeCustomerRole(ChangeCustomerRoleRequest $changeCustomerRoleRequest)
    {
        $validated = $changeCustomerRoleRequest->validated();
        $this->changeRole($changeCustomerRoleRequest->user, $validated['role']);

        return $this->successResponse(null, "Customer role updated successfully.", 200);
    }

    public function changeAdminRole(ChangeAdminRoleRequest $changeAdminRoleRequest)
    {
        $validated = $changeAdminRoleRequest->validated();
        $this->changeRole($changeAdminRoleRequest->user, $validated['role']);
        
        return $this->successResponse(null, "Admin role updated successfully.", 200);
    }
}
