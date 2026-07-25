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
        $this->get('http://gestione.fitframe.test/login')->assertOk();
    }

    public function test_users_can_authenticate_with_correct_credentials(): void
    {
        $user = User::factory()->superAdmin()->create();

        $response = $this->post('http://gestione.fitframe.test/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('http://gestione.fitframe.test/dashboard');
    }

    public function test_users_cannot_authenticate_with_wrong_password(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->post('http://gestione.fitframe.test/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('http://gestione.fitframe.test/')->assertRedirect('http://gestione.fitframe.test/login');
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->superAdmin()->create();

        $response = $this->actingAs($user)->post('http://gestione.fitframe.test/logout');

        $this->assertGuest();
        $response->assertRedirect('http://gestione.fitframe.test/login');
    }

    public function test_login_route_uses_a_five_per_minute_rate_limiter(): void
    {
        $limiter = RateLimiter::limiter('login');

        $this->assertNotNull($limiter, 'Nessun limiter "login" registrato — vedi AppServiceProvider::boot().');

        $limit = $limiter(Request::create('http://gestione.fitframe.test/login', 'POST'));

        $this->assertSame(5, $limit->maxAttempts);
    }
}
