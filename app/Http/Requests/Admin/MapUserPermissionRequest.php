<?php

namespace App\Http\Requests\Admin;

use App\Enums\PermissionEnum;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class MapUserPermissionRequest extends FormRequest
{
    public User $user;
    private Carbon $now;

    /** @var Permission[] */
    public array $rolePermissions;
    public array $mappedUserPermission = [];

    public function __construct() 
    {
        $this->now = now();
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return(new UserService())->hasPermission(PermissionEnum::MAP_USER_PERMISSIONS->value);
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
                $user = User::find($value);

                if(empty($user)) {
                    $fail("The user does not exist");
                    return;
                }

                $this->user = $user;
                $this->rolePermissions = Permission::where('type', $user->role->type)->get()->keyBy('id')->toArray();
            }],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['required', function($attribute, $value, $fail) {
                if(empty($this->user)) {
                    $fail("Role must be valid before checking permissions");
                    return;
                }

                if(!isset($this->rolePermissions[$value])) {
                    $fail("The permission does not exist for this user {$this->user->name}");
                    return;
                }

                $permission = $this->rolePermissions[$value];

                $this->mappedUserPermission[$this->user->id."-".$permission['id']] = [
                    'user_id' => $this->user->id,
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
            'user.required' => 'The user field is required.',
            'permissions.required' => 'You must provide at least one permission.',
            'permissions.*.required' => 'Each permission must be specified.',
        ];
    }
}
