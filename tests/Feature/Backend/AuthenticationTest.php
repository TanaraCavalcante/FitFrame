<?php

namespace Tests\Feature\Backend;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $this->get('/admin/login')->assertOk();
    }

    public function test_users_can_authenticate_with_correct_credentials(): void
    {
        $user = User::factory()->superAdmin()->create();

        $response = $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/admin');
    }

    public function test_users_cannot_authenticate_with_wrong_password(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->superAdmin()->create();

        $response = $this->actingAs($user)->post('/admin/logout');

        $this->assertGuest();
        $response->assertRedirect('/admin/login');
    }

    public function test_login_route_uses_a_five_per_minute_rate_limiter(): void
    {
        $limiter = RateLimiter::limiter('login');

        $this->assertNotNull($limiter, 'Nessun limiter "login" registrato — vedi AppServiceProvider::boot().');

        $limit = $limiter(Request::create('/admin/login', 'POST'));

        $this->assertSame(5, $limit->maxAttempts);
    }
}
