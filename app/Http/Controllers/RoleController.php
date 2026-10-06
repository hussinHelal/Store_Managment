<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignRoleRequest;
use App\Http\Requests\SaveRoleRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::query()->where('guard_name', 'web')->orderBy('name')
            ->paginate(15, ['*'], 'roles_page')
            ->withQueryString();
        $roleUserCounts = DB::table('model_has_roles')
            ->where('model_type', User::class)
            ->selectRaw('role_id, COUNT(*) as users_count')
            ->groupBy('role_id')
            ->pluck('users_count', 'role_id');
        $roles->getCollection()->each(function (Role $role) use ($roleUserCounts): void {
            $role->users_count = (int) $roleUserCounts->get($role->id, 0);
        });
        $users = User::query()->with('roles:id,name')->orderBy('name')->paginate(20);
        $assignableRoles = Role::query()->where('guard_name', 'web')->where('name', '!=', 'Superadmin')
            ->orderBy('name')->get(['id', 'name']);

        return view('roles.index', compact('roles', 'users', 'assignableRoles'));
    }

    public function create()
    {
        $role = null;
        $pages = config('access.pages');
        $permissions = collect();

        return view('roles.form', compact('role', 'pages', 'permissions'));
    }

    public function store(SaveRoleRequest $request)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated): void {
            $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);
            $role->syncPermissions($this->permissionsFor($validated['permissions'] ?? []));
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles.index')->with('success', 'تم إنشاء الدور بنجاح.');
    }

    public function edit(Role $role)
    {
        abort_unless($role->guard_name === 'web', 404);
        abort_if(Str::lower($role->name) === 'superadmin', 403);

        $pages = config('access.pages');
        $permissions = $role->permissions->keyBy('name');

        return view('roles.form', compact('role', 'pages', 'permissions'));
    }

    public function update(SaveRoleRequest $request, Role $role)
    {
        abort_unless($role->guard_name === 'web', 404);
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $role): void {
            $role->name = $validated['name'];
            $role->save();
            $role->syncPermissions($this->permissionsFor($validated['permissions'] ?? []));
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles.index')->with('success', 'تم تحديث الدور بنجاح.');
    }

    public function destroy(Role $role)
    {
        abort_unless($role->guard_name === 'web', 404);
        abort_if(in_array(Str::lower($role->name), ['admin', 'superadmin'], true), 403);

        if (DB::table('model_has_roles')->where('role_id', $role->id)
            ->where('model_type', User::class)->exists()) {
            return redirect()->route('roles.index')->with('error', 'لا يمكن حذف دور مرتبط بموظفين.');
        }

        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles.index')->with('success', 'تم حذف الدور بنجاح.');
    }

    public function assign(AssignRoleRequest $request, User $user)
    {
        $role = Role::query()->where('guard_name', 'web')->findOrFail($request->validated('role_id'));
        abort_if(Str::lower($role->name) === 'superadmin', 403);

        $changed = !$user->hasRole($role);

        DB::transaction(function () use ($role, $user): void {
            $user->syncRoles([$role]);
            $user->forceFill(['role' => $role->name === 'Admin' ? 'admin' : 'cashier'])->save();
        });

        if ($changed) {
            $user->tokens()->delete();
        }

        return redirect()->route('roles.index')->with('success', 'تم تحديث دور المستخدم بنجاح.');
    }

    public function updateLegacyAssignment(Request $request, User $user)
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);
        abort_if($user->system_account || $user->isSuperAdmin(), 403);

        $validated = $request->validate(['role' => ['required', 'in:admin,cashier']]);
        $roleName = $validated['role'] === 'admin' ? 'Admin' : 'Cashier';
        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        $user->syncRoles([$role]);
        $user->forceFill(['role' => $validated['role']])->save();
        $user->tokens()->delete();

        return redirect()->route('profile.index')->with('success', 'تم تحديث دور المستخدم بنجاح.');
    }

    private function permissionsFor(array $submitted): array
    {
        $permissions = [];

        foreach (config('access.pages', []) as $page => $label) {
            $abilities = $submitted[$page] ?? [];
            $manage = filter_var($abilities['manage'] ?? false, FILTER_VALIDATE_BOOL);
            $view = $manage || filter_var($abilities['view'] ?? false, FILTER_VALIDATE_BOOL);

            if ($view) {
                $permissions[] = Permission::firstOrCreate([
                    'name' => "page.{$page}.view",
                    'guard_name' => 'web',
                ]);
            }

            if ($manage) {
                $permissions[] = Permission::firstOrCreate([
                    'name' => "page.{$page}.manage",
                    'guard_name' => 'web',
                ]);
            }
        }

        return $permissions;
    }
}
