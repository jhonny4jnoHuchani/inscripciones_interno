<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    /**
     * Crear un nuevo rol con permisos asignados.
     */
    public function storeRole(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);

        if (! empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return back()->with('success', "Rol '{$role->name}' creado correctamente con sus permisos asignados.");
    }

    /**
     * Crear un nuevo permiso con su campo de descripción explicativa.
     */
    public function storePermission(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:permissions,name',
            'descripcion' => 'required|string|max:255',
        ]);

        $permission = Permission::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
            'descripcion' => $validated['descripcion'],
        ]);

        return back()->with('success', "Permiso '{$permission->name}' registrado con su descripción explicativa.");
    }

    /**
     * Sincronizar permisos de un rol existente.
     */
    public function syncPermissions(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return back()->with('success', "Permisos del rol '{$role->name}' actualizados.");
    }
}
