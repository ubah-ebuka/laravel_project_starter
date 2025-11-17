<?php 

namespace App\Http\Controllers\Admin;

use App\Models\Role;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\RolePermission;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use App\Http\Requests\Admin\AddRoleRequest;
use App\Http\Requests\Admin\MapRolePermissionRequest;
use Exception;

class RoleController extends Controller
{
    use ApiResponse;

    public function create(AddRoleRequest $addRoleRequest)
    {
        $payload = $addRoleRequest->validated();

        Role::create([
            'name' => $payload['name'],
            'identifier' => $payload['identifier'],
            'type' => $payload['type'],
            'description' => $payload['description']
        ]);

        return $this->successResponse(null, 'Role added successfully.', 200);
    }

    public function update(Role $role, Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $role->name = $validated['name'];
        $role->save();

        return $this->successResponse(null, 'Role updated successfully.', 200);
    }

    public function get(string|null $type = null)
    {
        $roles = Role::with('permissions')->when($type, fn($q) => $q->where('type', $type))->get();
        return $this->successResponse(RoleResource::collection($roles), 'Roles fetched successfully.', 200);
    }

    public function mapPermissions(MapRolePermissionRequest $mapRolePermissionRequest) 
    {
        $mappings = $mapRolePermissionRequest->mappedRolePermission;
        $role = $mapRolePermissionRequest->role;

        DB::beginTransaction();

        try{
            RolePermission::where(['role_id' => $role->id])->delete();
            RolePermission::insert(array_values($mappings));
            DB::commit();

            cache()->forget("compute_role_permissions_{$role->id}");

            return $this->successResponse(null, 'Roles mapped successfully.', 200);
        }catch(Exception $error) {
            DB::rollBack();
            return $this->failedResponse("Roles not mapped", 422);
        }
    }
}