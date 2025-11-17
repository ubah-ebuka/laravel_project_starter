<?php

namespace App\Http\Requests\Admin;

use App\Enums\PermissionEnum;
use App\Services\UserService;
use Illuminate\Foundation\Http\FormRequest;

class AddPermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (new UserService())->hasPermission(PermissionEnum::ADMIN_ADD_PERMISSION->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'identifier' => ['required', 'string', 'max:255', 'unique:permissions,identifier'],
            'type' => ['required', 'in:admin,customer'],
            'description' => ['required', 'string']
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
            'name.required' => 'Name is required.',
            'identifier.required' => 'Identifier address is required.',
            'identifier.unique' => 'This role identifier is already in use.',
            'type.required' => 'Type is required.',
            'type.in' => 'Type can only be admin or customer.',
            'description.required' => 'Description is required.'
        ];
    }
}
