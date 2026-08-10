<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        $users = User::orderBy('name')->get();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.form', [
            'user' => new User(),
            'roles' => $this->roles(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:'.implode(',', array_keys($this->roles()))],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'password' => $data['password'],
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        AuditLogger::log(
            'user.created',
            "User created: {$user->email}",
            $user,
            ['name' => $user->name, 'role' => $user->role]
        );

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', [
            'user' => $user,
            'roles' => $this->roles(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', 'in:'.implode(',', array_keys($this->roles()))],
        ]);

        $before = $user->only(['name', 'email', 'role']);
        $user->update($data);

        AuditLogger::log(
            'user.updated',
            "User updated: {$user->email}",
            $user,
            ['before' => $before, 'after' => $user->only(['name', 'email', 'role'])]
        );

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User updated successfully.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user->update(['password' => $request->password]);

        AuditLogger::log(
            'user.password_reset',
            "Password reset for {$user->email}",
            $user
        );

        return redirect()
            ->route('admin.users.index')
            ->with('status', "Password reset for {$user->name}.");
    }

    public function deactivate(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        if (! $user->isActive()) {
            return back()->with('error', 'User is already deactivated.');
        }

        $user->update(['is_active' => false]);

        AuditLogger::log(
            'user.deactivated',
            "User deactivated: {$user->email}",
            $user,
            ['name' => $user->name, 'role' => $user->role]
        );

        return redirect()
            ->route('admin.users.index')
            ->with('status', "{$user->name} has been deactivated.");
    }

    public function reactivate(User $user): RedirectResponse
    {
        if ($user->isActive()) {
            return back()->with('error', 'User is already active.');
        }

        $user->update(['is_active' => true]);

        AuditLogger::log(
            'user.reactivated',
            "User reactivated: {$user->email}",
            $user,
            ['name' => $user->name, 'role' => $user->role]
        );

        return redirect()
            ->route('admin.users.index')
            ->with('status', "{$user->name} has been reactivated.");
    }

    private function roles(): array
    {
        return [
            User::ROLE_USER => 'Staff',
            User::ROLE_ADMIN => 'Admin',
            User::ROLE_SUPER_USER => 'Super User',
        ];
    }
}
