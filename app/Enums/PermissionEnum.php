<?php

namespace App\Enums;

enum PermissionEnum: string
{
    case ACCESS_CUSTOMER_DASHBOARD = 'access_customer_dashboard';
    case ACCESS_ADMIN_DASHBOARD = 'access_admin_dashboard';
    case ADD_ADMIN = 'add_admin';
    case ADMIN_UPDATE_APP_SETTINGS = 'admin_update_app_settings';
    case MAP_ROLE_PERMISSIONS = 'map_role_permissions';
    case MAP_USER_PERMISSIONS = 'map_user_permissions';
    case ADMIN_GET_PAGINATED_CUSTOMERS = 'admin_get_paginated_customers';
    case ADMIN_GET_PAGINATED_ADMINS = 'admin_get_paginated_admins';
    case ADMIN_ADD_ROLE = 'admin_add_role';
    case ADMIN_UPDATE_USER_STATUS = 'admin_update_user_status';
    case ADMIN_ADD_PERMISSION = 'admin_add_permission';
    case ADMIN_CHANGE_CUSTOMER_ROLE = 'admin_change_customer_role';
    case ADMIN_CHANGE_ADMIN_ROLE = 'admin_change_admin_role';

    private const META = [
        self::ACCESS_CUSTOMER_DASHBOARD->value => [
            'type' => 'customer',
            'description' => 'Allows access to the customer dashboard',
            'label' => 'Customer Dashboard Access',
            'roles' => [RoleEnum::CUSTOMER]
        ],
        self::ACCESS_ADMIN_DASHBOARD->value => [
            'type' => 'admin',
            'description' => 'Allows access to the admin dashboard',
            'label' => 'Admin Dashboard Access',
            'roles' => [RoleEnum::ADMIN, RoleEnum::SUPER_ADMIN]
        ],
        self::ADD_ADMIN->value => [
            'type' => 'admin',
            'description' => 'Allows access to add admin',
            'label' => 'Add Admin',
            'roles' => [RoleEnum::SUPER_ADMIN]
        ],
        self::ADMIN_UPDATE_APP_SETTINGS->value => [
            'type' => 'admin',
            'description' => 'Allows access to update app settings',
            'label' => 'Update App Settings',
            'roles' => [RoleEnum::SUPER_ADMIN]
        ],
        self::MAP_ROLE_PERMISSIONS->value => [
            'type' => 'admin',
            'description' => 'Allows admin to map permissions to roles',
            'label' => 'Map Role Permissions',
            'roles' => [RoleEnum::SUPER_ADMIN]
        ],
        self::MAP_USER_PERMISSIONS->value => [
            'type' => 'admin',
            'description' => 'Allows admin to map permissions to user',
            'label' => 'Map User Permissions',
            'roles' => [RoleEnum::SUPER_ADMIN]
        ],
        self::ADMIN_GET_PAGINATED_CUSTOMERS->value => [
            'type' => 'admin',
            'description' => 'Allows admin to get paginated list of customers',
            'label' => 'Get Paginated List of Customers',
            'roles' => [RoleEnum::SUPER_ADMIN]
        ],
        self::ADMIN_GET_PAGINATED_ADMINS->value => [
            'type' => 'admin',
            'description' => 'Allows admin to get paginated list of admins',
            'label' => 'Get Paginated List of Admins',
            'roles' => [RoleEnum::SUPER_ADMIN]
        ],
        self::ADMIN_ADD_ROLE->value => [
            'type' => 'admin',
            'description' => 'Allows admin to add new roles',
            'label' => 'Add New Role',
            'roles' => [RoleEnum::SUPER_ADMIN]
        ],
        self::ADMIN_UPDATE_USER_STATUS->value => [
            'type' => 'admin',
            'description' => 'Allows admin to update user status',
            'label' => 'Admin Update User Status',
            'roles' => [RoleEnum::SUPER_ADMIN]
        ],
        self::ADMIN_ADD_PERMISSION->value => [
            'type' => 'admin',
            'description' => 'Allows admin to add new permissions',
            'label' => 'Add New Permission',
            'roles' => [RoleEnum::SUPER_ADMIN]
        ],
        self::ADMIN_CHANGE_CUSTOMER_ROLE->value => [
            'type' => 'admin',
            'description' => 'Allows admin to change customer role',
            'label' => 'Change Customer Role',
            'roles' => [RoleEnum::SUPER_ADMIN]
        ],
        self::ADMIN_CHANGE_ADMIN_ROLE->value => [
            'type' => 'admin',
            'description' => 'Allows admin to change admin role',
            'label' => 'Change Admin Role',
            'roles' => [RoleEnum::SUPER_ADMIN]
        ],
    ];

    private function meta(string $key): mixed
    {
        return self::META[$this->value][$key];
    }

    public function type(): string
    {
        return $this->meta('type');
    }

    public function description(): string
    {
        return $this->meta('description');
    }

    public function label(): string
    {
        return $this->meta('label');
    }

    public function roles(): array
    {
        return $this->meta('roles');
    }
}