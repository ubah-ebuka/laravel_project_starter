<?php

namespace App\Http\Requests\Customer;

use App\DTOs\OtpDTO;
use App\Enums\OtpActionTypeEnum;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UserConfirmPasswordResetRequest extends FormRequest
{
    public function __construct(private OtpService $otpService) 
    {
        
    }
    private ?User $user;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'password' => ['required', 'string', 'min:6', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).+$/', 'confirmed']
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => 'New password is required.',
            'password.confirmed' => 'New password confirmation does not match.'
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $user = User::where(['email' => $this->query('email'), 'type' => 'customer', 'status' => 'active'])->first();

                if (!$user) {
                    $validator->errors()->add('password', 'Invalid password reset link.');
                    return;
                }

                $isTokenValid = $this->otpService->validateOtp(recipient: $user->email, actionType: OtpActionTypeEnum::RESET_PASSWORD->value, token: $this->query('token'));

                if (!$isTokenValid) {
                    $validator->errors()->add('password', 'Invalid password reset link.');
                    return;
                }

                $this->user = $user;
            }
        ];
    }

    public function getValidatedUser(): ?User
    {
        return $this->user;
    }
}
