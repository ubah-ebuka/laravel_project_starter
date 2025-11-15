<?php

namespace App\Http\Requests\Customer;

use App\Models\User;
use Illuminate\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UserResetPasswordRequest extends FormRequest
{
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
            'email' => ['required', 'email'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email address is required.',
            'email.email' => 'Email address must be a valid email format.'
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $user = User::where(['email' => $this->input('email'), 'type' => 'customer', 'status' => 'active'])->first();

                if (!$user) {
                    $validator->errors()->add('email', 'This email address does not exist or is not active.');
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
