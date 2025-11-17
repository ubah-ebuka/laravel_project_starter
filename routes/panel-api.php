<?php

use App\Enums\PermissionEnum;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function() {
    Route::group(['prefix' => 'auth', 'as' => 'auth.'], function() {
        Route::post('login', [UserController::class, 'adminLogin'])
            ->name('login');

        Route::middleware(['auth:admin', 'web-api', 'admin-auth'])->group(function() {
            Route::get('logout', [UserController::class, 'adminLogout'])
            ->name('logout');

            Route::get('user', [UserController::class, 'user'])
                    ->name('user');
        });
    });

    Route::middleware(['auth:admin', 'web-api', 'admin-auth'])->group(function() {
        // Protected routes can be added here
        Route::post('password/change', [UserController::class, 'changePassword'])
                ->name('password.change');

        Route::group(['prefix' => 'admin', 'as' => 'admin.'], function() {
            Route::post('add', [AdminController::class, 'addAdmin'])
                ->name('add')->middleware('admin-has:'.PermissionEnum::ADD_ADMIN->value);
        });

        Route::group(['prefix' => 'role', 'as' => 'role.'], function() {
            Route::post('add', [RoleController::class, 'create'])
                ->name('add')->middleware('admin-has:'.PermissionEnum::ADMIN_ADD_ROLE->value);
            Route::post('update/{role}', [RoleController::class, 'update'])
                ->name('update')->middleware('admin-has:'.PermissionEnum::ADMIN_ADD_ROLE->value);
            Route::get('get/{type?}', [RoleController::class, 'get'])
                ->name('get')->middleware('admin-has:'.PermissionEnum::ADMIN_ADD_ROLE->value);
            Route::post('permission', [RoleController::class, 'mapPermissions'])
                ->name('permission')->middleware('admin-has:'.PermissionEnum::MAP_ROLE_PERMISSIONS->value);
        });

        Route::group(['prefix' => 'permission', 'as' => 'permission.'], function() {
            Route::post('add', [PermissionController::class, 'create'])
                ->name('add')->middleware('admin-has:'.PermissionEnum::ADMIN_ADD_PERMISSION->value);
            Route::post('update/{permission}', [PermissionController::class, 'update'])
                ->name('update')->middleware('admin-has:'.PermissionEnum::ADMIN_ADD_PERMISSION->value);
            Route::get('get/{type?}', [PermissionController::class, 'get'])
                ->name('get')->middleware('admin-has:'.PermissionEnum::ADMIN_ADD_PERMISSION->value);
        });

        Route::group(['prefix' => 'users', 'as' => 'users.'], function() {
            Route::get('customer', [UserController::class, 'customers'])
                ->name('customer')->middleware('admin-has:'.PermissionEnum::ADMIN_GET_PAGINATED_CUSTOMERS->value);
            Route::get('admin', [UserController::class, 'admins'])
                ->name('admin')->middleware('admin-has:'.PermissionEnum::ADMIN_GET_PAGINATED_ADMINS->value);
            Route::post('permission', [UserController::class, 'mapPermissions'])
                ->name('permission')->middleware('admin-has:'.PermissionEnum::MAP_USER_PERMISSIONS->value);
            Route::post('change-role/customer', [UserController::class, 'changeCustomerRole'])
                ->name('change-customer-role')->middleware('admin-has:'.PermissionEnum::ADMIN_CHANGE_CUSTOMER_ROLE->value);
            Route::post('change-role/admin', [UserController::class, 'changeAdminRole'])
                ->name('change-admin-role')->middleware('admin-has:'.PermissionEnum::ADMIN_CHANGE_ADMIN_ROLE->value);
        });
    });
});