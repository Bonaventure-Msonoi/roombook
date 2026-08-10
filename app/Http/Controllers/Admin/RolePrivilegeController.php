<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RolePrivilege;
use App\Services\AuditLogger;
use App\Support\Privileges;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RolePrivilegeController extends Controller
{
    public function edit(): View
    {
        $roles = Privileges::editableRoles();
        $privileges = Privileges::all();
        $labels = Privileges::roleLabels();

        $assigned = [];
        foreach ($roles as $role) {
            $assigned[$role] = RolePrivilege::forRole($role);
        }

        return view('admin.role-privileges.edit', compact('roles', 'privileges', 'labels', 'assigned'));
    }

    public function update(Request $request): RedirectResponse
    {
        $editableRoles = Privileges::editableRoles();
        $validPrivileges = array_keys(Privileges::all());

        $data = $request->validate([
            'privileges' => ['nullable', 'array'],
            'privileges.*' => ['array'],
            'privileges.*.*' => ['string', 'in:'.implode(',', $validPrivileges)],
        ]);

        $incoming = $data['privileges'] ?? [];

        DB::transaction(function () use ($editableRoles, $incoming) {
            foreach ($editableRoles as $role) {
                $selected = array_values(array_unique($incoming[$role] ?? []));

                RolePrivilege::query()->where('role', $role)->delete();

                foreach ($selected as $privilege) {
                    RolePrivilege::create([
                        'role' => $role,
                        'privilege' => $privilege,
                    ]);
                }

                RolePrivilege::clearCache($role);
            }
        });

        AuditLogger::log(
            'role_privileges.updated',
            'Role privileges updated',
            null,
            [
                'roles' => collect($editableRoles)->mapWithKeys(
                    fn (string $role) => [$role => $incoming[$role] ?? []]
                )->all(),
            ]
        );

        return redirect()
            ->route('admin.role-privileges.edit')
            ->with('status', 'Role privileges updated successfully.');
    }
}
