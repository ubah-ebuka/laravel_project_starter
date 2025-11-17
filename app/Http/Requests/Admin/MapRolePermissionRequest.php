<?php

namespace App\Http\Requests\Admin;

use App\Enums\PermissionEnum;
use App\Models\Permission;
use App\Models\Role;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class MapRolePermissionRequest extends FormRequest
{
    public Role $role;
    private Carbon $now;

    /** @var Permission[] */
    public array $rolePermissions;
    public array $mappedRolePermission = [];

    public function __construct() 
    {
        $this->now = now();
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return(new UserService())->hasPermission(PermissionEnum::MAP_ROLE_PERMISSIONS->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role' => ['required', function($attribute, $value, $fail) {
                $role = Role::find($value);

                if(empty($role)) {
                    $fail("The role does not exist");
                    return;
                }

                $this->role = $role;
                $this->rolePermissions = Permission::where('type', $role->type)->get()->keyBy('id')->toArray();
            }],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['required', function($attribute, $value, $fail) {
                if(empty($this->role)) {
                    $fail("Role must be valid before checking permissions");
                    return;
                }

                if(!isset($this->rolePermissions[$value])) {
                    $fail("The permission {$value} does not exist for this role {$this->role->name}");
                    return;
                }

                $permission = $this->rolePermissions[$value];

                $this->mappedRolePermission[$this->role->id."-".$permission['id']] = [
                    'role_id' => $this->role->id,
                    'permission_id' => $permission['id'],
                    'created_at' => $this->now,
                    'updated_at' => $this->now
                ];
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
            'role.required' => 'The role field is required.',
            'permissions.required' => 'You must provide at least one permission.',
            'permissions.*.required' => 'Each permission must be specified.',
        ];
    }
}
