<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\NewUserSignupAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register_but_remain_pending(): void
    {
        Notification::fake();

        $superUser = User::factory()->create([
            'role' => User::ROLE_SUPER_USER,
            'department' => 'Operations Dept',
        ]);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'department' => 'HR',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('login', absolute: false));

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('HR', $user->department);
        $this->assertTrue($user->isPendingApproval());
        $this->assertFalse($user->isActive());

        Notification::assertSentTo($superUser, NewUserSignupAlert::class);
    }
}
