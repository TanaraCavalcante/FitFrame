<?php

namespace Tests\Feature\Backend;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_forgot_password_screen_can_be_rendered(): void
    {
        $this->get('http://gestione.fitframe.test/password/forgot')->assertOk();
    }

    public function test_reset_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->superAdmin()->create();

        $this->post('http://gestione.fitframe.test/password/forgot', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->superAdmin()->create();
        $this->post('http://gestione.fitframe.test/password/forgot', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function (ResetPasswordNotification $notification) {
            $this->get('http://gestione.fitframe.test/password/reset/'.$notification->token)->assertOk();

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->superAdmin()->create();
        $this->post('http://gestione.fitframe.test/password/forgot', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function (ResetPasswordNotification $notification) use ($user) {
            $response = $this->post('http://gestione.fitframe.test/password/reset', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

            $response->assertRedirect('http://gestione.fitframe.test/login');

            $this->assertTrue(Auth::attempt(['email' => $user->email, 'password' => 'new-password']));

            return true;
        });
    }

    public function test_password_reset_routes_use_a_five_per_minute_rate_limiter(): void
    {
        $limiter = RateLimiter::limiter('password-reset');

        $this->assertNotNull($limiter, 'Nessun limiter "password-reset" registrato — vedi AppServiceProvider::boot().');

        $limit = $limiter(Request::create('/admin/password/forgot', 'POST'));

        $this->assertSame(5, $limit->maxAttempts);
    }
}
