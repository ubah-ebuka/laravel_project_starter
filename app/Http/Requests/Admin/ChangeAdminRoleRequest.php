<?php

namespace App\Http\Requests\Admin;

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Models\Role;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Http\FormRequest;

class ChangeAdminRoleRequest extends FormRequest
{
    public User $user;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (new UserService())->hasPermission(PermissionEnum::ADMIN_CHANGE_ADMIN_ROLE->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user' => ['required', function($attribute, $value, $fail) {
                $user = User::where(['type' => 'admin', 'id' => $value])->first();

                if(empty($user)) {
                    $fail("The user does not exist");
                    return;
                }

                $this->user = $user;
            }],
            'role' => ['required', function($attribute, $value, $fail) {
                $role = Role::where(['type' => "admin", 'id' => $value])->first();

                if(empty($role)) {
                    $fail("The role does not exist");
                    return;
                }

                if($role->identifier == RoleEnum::SUPER_ADMIN->value) {
                    $fail("This role cannot be given");
                    return;
                } 
            }]
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
            'user.required' => 'User is required.',
            'role.required' => 'Role is required.'
        ];
    }
}
