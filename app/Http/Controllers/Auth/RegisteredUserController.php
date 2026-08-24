<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\NewUserSignupAlert;
use App\Services\AuditLogger;
use App\Services\SuperUserAlerter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register', [
            'departments' => User::DEPARTMENTS,
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'department' => ['required', 'string', 'in:'.implode(',', User::DEPARTMENTS)],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'department' => $request->department,
            'password' => $request->password,
            'role' => User::ROLE_USER,
            'is_active' => false,
            'approved_at' => null,
            'email_verified_at' => now(),
        ]);

        AuditLogger::log(
            'user.registered',
            "User signed up and awaits approval: {$user->email}",
            $user,
            ['name' => $user->name, 'department' => $user->department]
        );

        SuperUserAlerter::notify(new NewUserSignupAlert($user));

        return redirect()
            ->route('login')
            ->with('status', 'Your account was created and is waiting for approval. You will be able to sign in once a super user approves it.');
    }
}
