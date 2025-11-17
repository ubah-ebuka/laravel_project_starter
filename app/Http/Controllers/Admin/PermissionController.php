<?php 

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AddPermissionRequest;
use App\Http\Resources\PermissionResource;
use App\Models\Permission;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    use ApiResponse;

    public function create(AddPermissionRequest $addPermissionRequest)
    {
        $payload = $addPermissionRequest->validated();

        Permission::create([
            'name' => $payload['name'],
            'identifier' => $payload['identifier'],
            'type' => $payload['type'],
            'description' => $payload['description']
        ]);

        return $this->successResponse(null, 'Permission added successfully.', 200);
    }

    public function update(Permission $permission, Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $permission->name = $validated['name'];
        $permission->save();

        return $this->successResponse(null, 'Permission updated successfully.', 200);
    }

    public function get(string|null $type = null)
    {
        $permissions = Permission::when($type, fn($q) => $q->where('type', $type))->get();
        return $this->successResponse(PermissionResource::collection($permissions), 'Permissions fetched successfully.', 200);
    }
}