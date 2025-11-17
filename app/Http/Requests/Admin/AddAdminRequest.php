<?php

namespace App\Http\Requests\Admin;

use App\Enums\PermissionEnum;
use App\Services\UserService;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class AddAdminRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (new UserService())->hasPermission(PermissionEnum::ADD_ADMIN->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->where(fn ($q) => $q->where('type', 'customer'))],
            'phone' => ['required', 'string', 'max:14', 'regex:/^(?:\+234|234|0)[0-9]{10}$/', Rule::unique('users', 'phone')->where(fn ($q) => $q->where('type', 'customer'))],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Email address must be a valid email format.',
            'email.unique' => 'This email address is already in use.',
            'phone.required' => 'Phone number is required.',
            'phone.regex' => 'Phone number must be a valid Nigerian phone number format.',
            'phone.unique' => 'This phone number is already in use.',
        ];
    }
}
