<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Enums\PermissionEnum;
use App\Models\RolePermission;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment('local', 'staging', 'development')) {
            Permission::truncate();
            RolePermission::truncate();

            foreach (PermissionEnum::cases() as $permission) {
                $permissionType = $permission->type();
                $permissionVal = $permission->value;

                $createdPermission = Permission::firstOrCreate(
                    [
                        'identifier' => $permissionVal,
                    ],
                    [
                        'name' => $permission->label(),
                        'type' => $permissionType,
                        'description' => $permission->description()
                    ]
                );

                if ($permission->roles()) {
                    foreach ($permission->roles() as $roleEnum) {
                        $role = Role::where('identifier', $roleEnum->value)->first();
                        RolePermission::firstOrCreate([
                            'role_id' => $role->id,
                            'permission_id' => $createdPermission->id
                        ]);
                    }
                }
            }
        }
    }
}
