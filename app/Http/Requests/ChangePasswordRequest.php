<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;

class ChangePasswordRequest extends FormRequest
{
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
            'old' => ['required', 'string'],
            'new' => ['required', 'string', 'min:6', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).+$/', 'confirmed']
        ];
    }

    public function messages(): array
    {
        return [
            'old.required' => 'Old password is required.',
            'new.required' => 'New password is required.',
            'password.min' => 'New Password must be at least 6 characters long.',
            'password.regex' => 'New Password must contain at least one uppercase letter, one lowercase letter, and one special character.'
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $user = request()->user();

                if (!Hash::check($this->input('old'), $user->password)) {
                    $validator->errors()->add('old', 'Old password is incorrect.');
                    return;
                }
            }
        ];
    }
}
