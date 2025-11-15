<?php

namespace App\Http\Controllers\Customer;

use App\DTOs\OtpDTO;
use App\Models\User;
use App\DTOs\UserDTO;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Enums\CurrencyCodeEnum;
use App\Enums\OtpActionTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\UserConfirmPasswordResetRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Customer\UserResource;
use App\Http\Requests\Customer\UserLoginRequest;
use App\Http\Requests\Customer\UserRegistrationRequest;
use App\Http\Requests\Customer\UserResetPasswordRequest;
use App\Models\Permission;
use App\Notifications\GeneralNoty;
use App\Services\NotificationService;
use App\Services\OtpService;
use App\Services\SmsService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    use ApiResponse;

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
        Auth::login($user);
        
        return $this->user(message: "Registered successfully.");
    }

    public function login(UserLoginRequest $userLoginRequest)
    {
        $payload = $userLoginRequest->validated();
        $user = $userLoginRequest->getValidatedUser();
        Auth::login($user);
        
        return $this->user(message: "Logged in successfully.");
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->successResponse(null, 'User logged out successfully.', 200);
    }

    public function user($message = "User profile retrieved successfully.")
    {
        $user = request()->user();

        $permissionsByRole = Permission::where("permissions.type", 'customer')->leftJoin('role_permissions', 'permissions.id', '=', 'role_permissions.permission_id')
            ->where('role_permissions.role_id', $user->role_id)
            ->select('permissions.identifier')
            ->get()->pluck('identifier')->toArray();

        $permissionsByUser = Permission::where("permissions.type", 'customer')->leftJoin('user_permissions', 'permissions.id', '=', 'user_permissions.permission_id')
            ->where('user_permissions.user_id', $user->id)
            ->select('permissions.identifier')
            ->get()->pluck('identifier')->toArray();

        $permissions = array_merge($permissionsByRole, $permissionsByUser);

        $response = [
            'user' => new UserResource($user),
            'permissions' => $permissions
        ];

        return $this->successResponse($response, $message, 200);
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
                'resetUrl' => $resetUrl
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
                'verificationUrl' => $verificationUrl
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
}
