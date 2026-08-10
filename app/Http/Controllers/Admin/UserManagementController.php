<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        $pendingUsers = User::pendingApproval()->orderByDesc('created_at')->get();
        $users = User::approved()->orderBy('name')->get();

        return view('admin.users.index', compact('pendingUsers', 'users'));
    }

    public function create(): View
    {
        return view('admin.users.form', [
            'user' => new User(),
            'roles' => $this->roles(),
            'departments' => User::DEPARTMENTS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'department' => ['required', Rule::in(User::DEPARTMENTS)],
            'role' => ['required', 'in:'.implode(',', array_keys($this->roles()))],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'department' => $data['department'],
            'role' => $data['role'],
            'password' => $data['password'],
            'email_verified_at' => now(),
            'is_active' => true,
            'approved_at' => now(),
        ]);

        AuditLogger::log(
            'user.created',
            "User created: {$user->email}",
            $user,
            ['name' => $user->name, 'role' => $user->role, 'department' => $user->department]
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
            'departments' => User::DEPARTMENTS,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'department' => ['required', Rule::in(User::DEPARTMENTS)],
            'role' => ['required', 'in:'.implode(',', array_keys($this->roles()))],
        ]);

        $before = $user->only(['name', 'email', 'department', 'role']);
        $user->update($data);

        AuditLogger::log(
            'user.updated',
            "User updated: {$user->email}",
            $user,
            ['before' => $before, 'after' => $user->only(['name', 'email', 'department', 'role'])]
        );

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User updated successfully.');
    }

    public function approve(Request $request, User $user): RedirectResponse
    {
        if ($user->isApproved()) {
            return back()->with('error', 'User is already approved.');
        }

        if ($user->isRejected()) {
            return back()->with('error', 'This signup was rejected. Create a new account instead.');
        }

        $data = $request->validate([
            'role' => ['required', 'in:'.implode(',', array_keys($this->roles()))],
        ]);

        $user->update([
            'role' => $data['role'],
            'is_active' => true,
            'approved_at' => now(),
            'rejected_at' => null,
        ]);

        AuditLogger::log(
            'user.approved',
            "User approved: {$user->email}",
            $user,
            ['name' => $user->name, 'role' => $user->role]
        );

        return redirect()
            ->route('admin.users.index')
            ->with('status', "{$user->name} has been approved and can now sign in.");
    }

    public function reject(User $user): RedirectResponse
    {
        if ($user->isApproved()) {
            return back()->with('error', 'Approved users cannot be rejected. Deactivate them instead.');
        }

        if ($user->isRejected()) {
            return back()->with('error', 'This signup is already rejected.');
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot reject your own account.');
        }

        $user->update([
            'is_active' => false,
            'rejected_at' => now(),
        ]);

        AuditLogger::log(
            'user.rejected',
            "Signup rejected: {$user->email}",
            $user,
            ['name' => $user->name, 'email' => $user->email]
        );

        return redirect()
            ->route('admin.users.index')
            ->with('status', "Signup for {$user->name} was rejected.");
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

        if ($user->isPendingApproval()) {
            return back()->with('error', 'Pending signups should be approved or rejected, not deactivated.');
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
        if ($user->isPendingApproval()) {
            return back()->with('error', 'Pending signups must be approved first.');
        }

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
