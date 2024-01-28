<?php

namespace App\Http\Controllers\Permissions;

use Illuminate\Http\Request;
use App\Response\ApiResponse;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    //

    public function allPermission()
    {
        $permissions = Permission::where('appKey',539)->select('id','name')->get();

        return response()->json($permissions);
    }

    public function allPermissionsForRole($roleId)
    {
        // Find the role
        $role = Role::find($roleId);

        if (!$role) {
            return response()->json(['error' => 'Role not found'], 404);
        }

        // Get all permissions for the role
        $permissions = $role->permissions()->pluck('name');

        return response()->json(['permissions' => $permissions]);
    }
    public function allRoles()
    {
        // Get all roles
        $roles = Role::where('appKey',539)->pluck('name','id');

        return response()->json(['roles' => $roles]);
    }


    public function permissionsForRole($roleName)
    {
        $role = Role::where('appKey', 539)->where('name', $roleName)->first();

        if (!$role) {
            return (new ApiResponse(404, __('Role not found.'), []))->send();
        }

        // Get all permissions for the role
        $permissions = $role->permissions->pluck('name');

        return response()->json(['permissions' => $permissions]);
    }
	
   public function updateRolePermissions(Request $request, $roleName)
{
    // Find the role by name and appKey
    $role = Role::where('appKey', 539)->where('name', $roleName)->first();

    if (!$role) {
        return (new ApiResponse(404, __('Role not found.'), []))->send();
    }

    // Validate the request data
    $request->validate([
        'permissions' => 'array|required',
    ]);

    // Get the existing permissions
    $existingPermissions = $role->permissions->pluck('name')->toArray();

    // Compare existing permissions with new permissions
    $permissionsToRemove = array_diff($existingPermissions, $request->input('permissions'));

    // Remove old permissions
    foreach ($permissionsToRemove as $permission) {
        $role->revokePermissionTo($permission);
    }

    // Add new permissions
    $role->givePermissionTo($request->input('permissions'));

    // Retrieve and return the updated permissions
    $updatedPermissions = $role->permissions->pluck('name');

    return (new ApiResponse(200, __('Role permissions updated successfully.'), ['permissions' => $updatedPermissions]))->send();
}
	
	 public function deleteRolePermissions(Request $request, $roleName)
    {
        // Find the role by name and appKey
        $role = Role::where('appKey', 539)->where('name', $roleName)->first();

        if (!$role) {
            return (new ApiResponse(404, __('Role not found.'), []))->send();
        }

        // Validate the request data
        $request->validate([
            'permissions' => 'array|required',
        ]);

        // Revoke the specified permissions from the role
        $role->revokePermissionTo($request->input('permissions'));

        // Retrieve and return the updated permissions
        $updatedPermissions = $role->permissions->pluck('name');

        return (new ApiResponse(200, __('Role permissions deleted successfully.'), ['permissions' => $updatedPermissions]))->send();
    }
	
	  public function createRoleWithPermissions(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'role_name' => 'required|string|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        // Create a new role
        $role = Role::create(['name' => $request->input('role_name'),'guard_name' => 'web', 'appKey' => 539]);

        // Assign permissions to the role
        $permissions = $request->input('permissions');
        $role->syncPermissions($permissions);

        return(new ApiResponse(200, __('Role created successfully.'), ['permissions' => $permissions]))->send();
    }
	
	public function deleteRole(Request $request, $roleId)
    {
        // Validate the incoming request data


        // Find the role by ID
        $role = Role::where('appKey', 539)->find($roleId);

        // Check if the role exists
        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        // Delete the role
        if( $role) {
            $role->forceDelete();
        }

        return response()->json(['message' => 'Role deleted successfully'], 200);
    }

}