# Pannello Admin Backend — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a login-protected admin area to FitFrame where a `super_admin` manages Strutture (Gym) and Utenti (gym_admin + super_admin accounts), and a `gym_admin` will later manage their own gym's content (out of scope here).

**Architecture:** New `role`/`gym_id` columns on `users`. Authorization via Policies (`GymPolicy`, `UserPolicy`), not a global scope. A separate `routes/backend.php` (prefix `/admin`) registered alongside `routes/web.php`, sharing the `web` middleware group but never the app's `ResolveGym` tenant-resolution middleware (moved from a global append to an explicit middleware on the public route only). Auth is hand-rolled (no Breeze) — login/logout plus core-Laravel password reset by email — because the project has no JS build tooling (Bootstrap via CDN only) and doesn't need registration/email-verification. `lab404/laravel-impersonate` lets `super_admin` log in as a `gym_admin`. `spatie/laravel-medialibrary` is installed now (approved dependency) but not used until the hero-media spec.

**Tech Stack:** Laravel 12, PHP 8.3, PHPUnit 11, Bootstrap 5.3 (CDN), `lab404/laravel-impersonate`, `spatie/laravel-medialibrary`.

**Spec:** `docs/superpowers/specs/2026-07-25-backend-admin-panel-design.md`

---

## File Structure

**Migrations**
- Create: `database/migrations/2026_07_25_120000_add_role_and_gym_id_to_users_table.php`

**Enums**
- Create: `app/Enums/UserRole.php`

**Models**
- Modify: `app/Models/User.php`
- Modify: `app/Models/Gym.php`

**Policies**
- Create: `app/Policies/GymPolicy.php`
- Create: `app/Policies/UserPolicy.php`

**Notifications**
- Create: `app/Notifications/ResetPasswordNotification.php`

**Providers**
- Modify: `app/Providers/AppServiceProvider.php`

**Requests**
- Create: `app/Http/Requests/Backend/StoreGymRequest.php`
- Create: `app/Http/Requests/Backend/UpdateGymRequest.php`
- Create: `app/Http/Requests/Backend/StoreGymAdminRequest.php`
- Create: `app/Http/Requests/Backend/UpdateGymAdminRequest.php`
- Create: `app/Http/Requests/Backend/StoreSuperAdminRequest.php`
- Create: `app/Http/Requests/Backend/UpdateSuperAdminRequest.php`

**Controllers**
- Create: `app/Http/Controllers/Backend/AuthenticatedSessionController.php`
- Create: `app/Http/Controllers/Backend/PasswordResetLinkController.php`
- Create: `app/Http/Controllers/Backend/NewPasswordController.php`
- Create: `app/Http/Controllers/Backend/DashboardController.php`
- Create: `app/Http/Controllers/Backend/GymController.php`
- Create: `app/Http/Controllers/Backend/GymAdminController.php`
- Create: `app/Http/Controllers/Backend/SuperAdminController.php`

**Routes**
- Create: `routes/backend.php`
- Modify: `routes/web.php`
- Modify: `bootstrap/app.php`

**Views**
- Create: `resources/views/backend/layouts/app.blade.php`
- Create: `resources/views/backend/auth/login.blade.php`
- Create: `resources/views/backend/auth/forgot-password.blade.php`
- Create: `resources/views/backend/auth/reset-password.blade.php`
- Create: `resources/views/backend/dashboard.blade.php`
- Create: `resources/views/backend/strutture/{index,create,edit,_form}.blade.php`
- Create: `resources/views/backend/utenti/{index,create,edit,_form}.blade.php`
- Create: `resources/views/backend/super-admin/{index,create,edit,_form}.blade.php`

**Assets**
- Create: `public/css/backend.css`
- Create: `public/js/backend.js`

**Factories**
- Modify: `database/factories/UserFactory.php`

**Tests**
- Create: `tests/Unit/Policies/GymPolicyTest.php`
- Create: `tests/Unit/Policies/UserPolicyTest.php`
- Create: `tests/Feature/Backend/RouteWiringTest.php`
- Create: `tests/Feature/Backend/AuthenticationTest.php`
- Create: `tests/Feature/Backend/PasswordResetTest.php`
- Create: `tests/Feature/Backend/DashboardTest.php`
- Create: `tests/Feature/Backend/GymControllerTest.php`
- Create: `tests/Feature/Backend/GymAdminControllerTest.php`
- Create: `tests/Feature/Backend/SuperAdminControllerTest.php`
- Create: `tests/Feature/Backend/ImpersonationTest.php`

---

### Task 1: Install approved dependencies

**Files:**
- Modify: `composer.json`, `composer.lock`
- Create: `config/laravel-impersonate.php` (published)
- Create: a `media` migration (published, name assigned by the package)

- [ ] **Step 1: Check required PHP extensions for medialibrary**

Run: `php -m | grep -iE "exif|fileinfo"`
Expected: both `exif` and `fileinfo` listed. If either is missing, enable it in `php.ini` before continuing (medialibrary's composer install will fail otherwise).

- [ ] **Step 2: Require the two packages**

Run:
```bash
composer require lab404/laravel-impersonate spatie/laravel-medialibrary
```
Expected: both resolve and install without conflicts (both declare Laravel 12 support).

- [ ] **Step 3: Publish impersonate config**

Run: `php artisan vendor:publish --tag=impersonate`
Expected: `Copied File [...] To [/config/laravel-impersonate.php]`.

- [ ] **Step 4: Publish and run medialibrary's migration**

Run:
```bash
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"
php artisan migrate
```
Expected: a `xxxx_xx_xx_create_media_table.php` migration is copied into `database/migrations/`, then migrates successfully (creates a `media` table, unused until the hero-media spec).

- [ ] **Step 5: Commit**

```bash
git add composer.json composer.lock config/laravel-impersonate.php database/migrations/*_create_media_table.php
git commit -m "chore: install laravel-impersonate and medialibrary"
```

---

### Task 2: `role`/`gym_id` on users, `UserRole` enum, `User` model, impersonation contract

**Files:**
- Create: `app/Enums/UserRole.php`
- Create: `database/migrations/2026_07_25_120000_add_role_and_gym_id_to_users_table.php`
- Modify: `app/Models/User.php`
- Modify: `database/factories/UserFactory.php`
- Test: `tests/Unit/UserRoleTest.php`

- [ ] **Step 1: Write the failing test for the enum**

```php
<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use PHPUnit\Framework\TestCase;

class UserRoleTest extends TestCase
{
    public function test_values_match_database_strings(): void
    {
        $this->assertSame('super_admin', UserRole::SuperAdmin->value);
        $this->assertSame('gym_admin', UserRole::GymAdmin->value);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact tests/Unit/UserRoleTest.php`
Expected: FAIL — `Class "App\Enums\UserRole" not found`.

- [ ] **Step 3: Create the enum**

```php
<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case GymAdmin = 'gym_admin';
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --compact tests/Unit/UserRoleTest.php`
Expected: PASS.

- [ ] **Step 5: Create the migration**

```bash
php artisan make:migration add_role_and_gym_id_to_users_table --table=users --no-interaction
```
Rename the generated file to `2026_07_25_120000_add_role_and_gym_id_to_users_table.php` (keeps chronological order after the existing `2026_07_24_*` migrations) and replace its contents:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'role')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->after('password');
            $table->foreignId('gym_id')->nullable()->after('role')->constrained()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('gym_id');
            $table->dropColumn('role');
        });
    }
};
```

Guardia `Schema::hasColumn` per idempotenza, coerente con il pattern già usato nelle migration esistenti (`Schema::hasTable` in `gyms`/`domains` — vedi quelle migration per lo stesso stile).

`restrictOnDelete()`: a Gym with assigned `gym_admin` users cannot be deleted at the database level until they're reassigned/removed (Task 8 also enforces this at the application level with a friendly message).

- [ ] **Step 6: Run migration**

Run: `php artisan migrate`
Expected: `Migrating: ..._add_role_and_gym_id_to_users_table` / `Migrated:  ...` with no errors.

- [ ] **Step 7: Update the `User` model**

```php
<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Lab404\Impersonate\Models\Impersonate;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Impersonate, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'gym_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }

    /**
     * Solo un super_admin può impersonare un altro utente.
     */
    public function canImpersonate(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    /**
     * Solo un gym_admin può essere impersonato (mai un altro super_admin).
     */
    public function canBeImpersonated(): bool
    {
        return $this->role === UserRole::GymAdmin;
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }
}
```

- [ ] **Step 8: Update `UserFactory` with role defaults and a `superAdmin` state**

```php
<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Gym;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => UserRole::GymAdmin,
            'gym_id' => Gym::factory(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is a super_admin (no gym).
     */
    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::SuperAdmin,
            'gym_id' => null,
        ]);
    }
}
```

Note: `sendPasswordResetNotification` references `App\Notifications\ResetPasswordNotification`, created in Task 6 — until then this method is unreachable dead code (nothing calls it), so it doesn't break anything now.

- [ ] **Step 9: Run the full existing test suite to check for regressions**

Run: `php artisan test --compact`
Expected: all previously-passing tests still PASS (existing factories/tests didn't reference `role`/`gym_id`, so nothing should break).

- [ ] **Step 10: Commit**

```bash
git add app/Enums/UserRole.php app/Models/User.php database/migrations/2026_07_25_120000_add_role_and_gym_id_to_users_table.php database/factories/UserFactory.php tests/Unit/UserRoleTest.php
git commit -m "feat: add role/gym_id to users, wire impersonation contract"
```

---

### Task 3: `Gym` model helpers and Policies

**Files:**
- Modify: `app/Models/Gym.php`
- Create: `app/Policies/GymPolicy.php`
- Create: `app/Policies/UserPolicy.php`
- Test: `tests/Unit/Policies/GymPolicyTest.php`
- Test: `tests/Unit/Policies/UserPolicyTest.php`
- Test: `tests/Unit/Models/GymAvailableThemesTest.php`

- [ ] **Step 1: Write the failing test for `Gym::availableThemes()`**

```php
<?php

namespace Tests\Unit\Models;

use App\Models\Gym;
use Tests\TestCase;

class GymAvailableThemesTest extends TestCase
{
    public function test_lists_theme_names_excluding_base(): void
    {
        $themes = Gym::availableThemes();

        $this->assertContains('pulse', $themes);
        $this->assertContains('iron-house', $themes);
        $this->assertContains('zenflow', $themes);
        $this->assertNotContains('base', $themes);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact tests/Unit/Models/GymAvailableThemesTest.php`
Expected: FAIL — `Call to undefined method App\Models\Gym::availableThemes()`.

- [ ] **Step 3: Add `availableThemes()` and `admins()` to `Gym`**

```php
<?php

namespace App\Models;

use Database\Factories\GymFactory;
use Igaster\LaravelTheme\Facades\Theme;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Gym extends Model
{
    /** @use HasFactory<GymFactory> */
    use HasFactory;

    protected $fillable = ['name', 'slug'];

    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }

    public function gymSections(): HasMany
    {
        return $this->hasMany(GymSection::class);
    }

    public function contact(): HasOne
    {
        return $this->hasOne(Contact::class);
    }

    public function contents(): HasMany
    {
        return $this->hasMany(Content::class);
    }

    public function gymClasses(): HasMany
    {
        return $this->hasMany(GymClass::class);
    }

    public function plans(): HasMany
    {
        return $this->hasMany(Plan::class);
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }

    public function personalTrainers(): HasMany
    {
        return $this->hasMany(PersonalTrainer::class);
    }

    /**
     * Utenti gym_admin assegnati a questa struttura.
     */
    public function admins(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Recupera un testo libero da `contents` per questa palestra (es. hero_headline).
     * Se la chiave non esiste per la palestra, ritorna il default del tema base.
     */
    public function content(string $key, string $default = ''): string
    {
        return $this->contents->firstWhere('key', $key)?->value ?? $default;
    }

    /**
     * Nomi dei temi installati (cartelle con theme.json), esclusa `base`
     * che non ha asset propri e non è mai assegnabile a una Gym reale.
     *
     * @return array<int, string>
     */
    public static function availableThemes(): array
    {
        return Theme::all()
            ->pluck('name')
            ->reject(fn (string $name) => $name === 'base')
            ->values()
            ->all();
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --compact tests/Unit/Models/GymAvailableThemesTest.php`
Expected: PASS.

- [ ] **Step 5: Write the failing tests for `GymPolicy`**

```php
<?php

namespace Tests\Unit\Policies;

use App\Models\Gym;
use App\Models\User;
use App\Policies\GymPolicy;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class GymPolicyTest extends TestCase
{
    use LazilyRefreshDatabase;

    private GymPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new GymPolicy;
    }

    public function test_super_admin_can_view_any_and_manage_any_gym(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $gym = Gym::factory()->create();

        $this->assertTrue($this->policy->viewAny($superAdmin));
        $this->assertTrue($this->policy->create($superAdmin));
        $this->assertTrue($this->policy->update($superAdmin, $gym));
        $this->assertTrue($this->policy->delete($superAdmin, $gym));
        $this->assertTrue($this->policy->manage($superAdmin, $gym));
    }

    public function test_gym_admin_cannot_view_any_or_write_strutture(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();

        $this->assertFalse($this->policy->viewAny($gymAdmin));
        $this->assertFalse($this->policy->create($gymAdmin));
        $this->assertFalse($this->policy->update($gymAdmin, $gym));
        $this->assertFalse($this->policy->delete($gymAdmin, $gym));
    }

    public function test_gym_admin_can_manage_only_their_own_gym(): void
    {
        $ownGym = Gym::factory()->create();
        $otherGym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($ownGym)->create();

        $this->assertTrue($this->policy->manage($gymAdmin, $ownGym));
        $this->assertFalse($this->policy->manage($gymAdmin, $otherGym));
    }
}
```

- [ ] **Step 6: Run test to verify it fails**

Run: `php artisan test --compact tests/Unit/Policies/GymPolicyTest.php`
Expected: FAIL — `Class "App\Policies\GymPolicy" not found`.

- [ ] **Step 7: Create `GymPolicy`**

```php
<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Gym;
use App\Models\User;

class GymPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::SuperAdmin;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::SuperAdmin;
    }

    public function update(User $user, Gym $gym): bool
    {
        return $user->role === UserRole::SuperAdmin;
    }

    public function delete(User $user, Gym $gym): bool
    {
        return $user->role === UserRole::SuperAdmin;
    }

    /**
     * Usata dai controller "Contenuti" (spec futura): super_admin gestisce
     * qualunque struttura, gym_admin solo la propria.
     */
    public function manage(User $user, Gym $gym): bool
    {
        return $user->role === UserRole::SuperAdmin || $user->gym_id === $gym->id;
    }
}
```

- [ ] **Step 8: Run test to verify it passes**

Run: `php artisan test --compact tests/Unit/Policies/GymPolicyTest.php`
Expected: PASS.

- [ ] **Step 9: Write the failing tests for `UserPolicy`**

```php
<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    use LazilyRefreshDatabase;

    private UserPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new UserPolicy;
    }

    public function test_super_admin_can_manage_users(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $otherSuperAdmin = User::factory()->superAdmin()->create();

        $this->assertTrue($this->policy->viewAny($superAdmin));
        $this->assertTrue($this->policy->create($superAdmin));
        $this->assertTrue($this->policy->update($superAdmin, $otherSuperAdmin));
        $this->assertTrue($this->policy->delete($superAdmin, $otherSuperAdmin));
    }

    public function test_super_admin_cannot_delete_themselves(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->assertFalse($this->policy->delete($superAdmin, $superAdmin));
    }

    public function test_gym_admin_cannot_manage_users(): void
    {
        $gymAdmin = User::factory()->create();
        $anotherGymAdmin = User::factory()->create();

        $this->assertFalse($this->policy->viewAny($gymAdmin));
        $this->assertFalse($this->policy->create($gymAdmin));
        $this->assertFalse($this->policy->update($gymAdmin, $anotherGymAdmin));
        $this->assertFalse($this->policy->delete($gymAdmin, $anotherGymAdmin));
    }
}
```

- [ ] **Step 10: Run test to verify it fails**

Run: `php artisan test --compact tests/Unit/Policies/UserPolicyTest.php`
Expected: FAIL — `Class "App\Policies\UserPolicy" not found`.

- [ ] **Step 11: Create `UserPolicy`**

```php
<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::SuperAdmin;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::SuperAdmin;
    }

    public function update(User $user, User $target): bool
    {
        return $user->role === UserRole::SuperAdmin;
    }

    public function delete(User $user, User $target): bool
    {
        return $user->role === UserRole::SuperAdmin && $user->id !== $target->id;
    }
}
```

- [ ] **Step 12: Run test to verify it passes**

Run: `php artisan test --compact tests/Unit/Policies/UserPolicyTest.php`
Expected: PASS.

- [ ] **Step 13: Commit**

```bash
git add app/Models/Gym.php app/Policies/GymPolicy.php app/Policies/UserPolicy.php tests/Unit/Policies tests/Unit/Models/GymAvailableThemesTest.php
git commit -m "feat: authorization policies for Gym and User management"
```

---

### Task 4: Move `ResolveGym` off the global middleware stack, scaffold `routes/backend.php`

**Files:**
- Modify: `bootstrap/app.php`
- Modify: `routes/web.php`
- Create: `routes/backend.php`
- Test: `tests/Feature/Backend/RouteWiringTest.php`

- [ ] **Step 1: Write the failing regression test**

```php
<?php

namespace Tests\Feature\Backend;

use App\Models\Domain;
use App\Models\Gym;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class RouteWiringTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_public_homepage_still_resolves_gym_by_domain(): void
    {
        $gym = Gym::factory()->create(['slug' => 'pulse']);
        Domain::factory()->for($gym)->create(['domain' => 'academiaa.test']);

        $response = $this->get('http://academiaa.test/');

        $response->assertOk();
    }

    public function test_admin_prefix_is_reserved_and_returns_404_with_no_routes_yet(): void
    {
        $response = $this->get('/admin/nonexistent');

        $response->assertNotFound();
    }
}
```

`database/factories/DomainFactory.php` already exists (`gym_id => Gym::factory()`, `domain => fake()->unique()->domainName()`), so `Domain::factory()->for($gym)->create(['domain' => 'academiaa.test'])` works as-is — nothing to create here.

- [ ] **Step 2: Run test to verify it fails (or passes for the wrong reason)**

Run: `php artisan test --compact tests/Feature/Backend/RouteWiringTest.php`
Expected: the homepage test currently PASSes already (no change yet), and the `/admin/nonexistent` test also currently returns 404 (no `/admin` routes exist at all yet, so this one already passes too) — this task's real check is that both **keep** passing after the refactor below. Run once now to confirm the baseline, then again after Step 5.

- [ ] **Step 3: Update `bootstrap/app.php`**

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    // Definisce i file delle rotte: web (browser) e console (comandi artisan).
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            // Rotte dell'area admin (/admin): stesso gruppo 'web' (sessioni/CSRF),
            // ma SENZA ResolveGym — vedi routes/web.php, dove resta applicato
            // solo alla rotta pubblica.
            Route::middleware('web')->group(base_path('routes/backend.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn (Request $request) => route('backend.login'));
        $middleware->redirectUsersTo(fn (Request $request) => route('backend.dashboard'));
    })
    // Personalizzazione della gestione delle eccezioni (vuoto = comportamento di default).
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
```

- [ ] **Step 4: Update `routes/web.php`**

```php
<?php

use App\Http\Controllers\GymController;
use App\Http\Middleware\ResolveGym;
use Illuminate\Support\Facades\Route;

Route::middleware(ResolveGym::class)->group(function () {
    Route::get('/', [GymController::class, 'index']);
});
```

- [ ] **Step 5: Create `routes/backend.php` skeleton**

```php
<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('backend.')->group(function () {
    Route::middleware('guest')->group(function () {
        //
    });

    Route::middleware('auth')->group(function () {
        //
    });
});
```

- [ ] **Step 6: Run test to verify it still passes**

Run: `php artisan test --compact tests/Feature/Backend/RouteWiringTest.php`
Expected: PASS — public homepage still resolves via `ResolveGym` (now applied directly, not globally), and `/admin/*` returns 404 (routes not defined yet, but the prefix/group scaffolding parses and boots without error).

- [ ] **Step 7: Run the full suite for regressions**

Run: `php artisan test --compact`
Expected: all PASS (no route in the app currently depends on `auth`/`guest` middleware redirect targets, since nothing uses them yet).

- [ ] **Step 8: Commit**

```bash
git add bootstrap/app.php routes/web.php routes/backend.php tests/Feature/Backend/RouteWiringTest.php
git commit -m "refactor: scope ResolveGym to the public route, scaffold admin routes"
```

---

### Task 5: Manual login/logout

**Files:**
- Create: `app/Http/Controllers/Backend/AuthenticatedSessionController.php`
- Create: `resources/views/backend/auth/login.blade.php`
- Modify: `routes/backend.php`
- Modify: `app/Providers/AppServiceProvider.php`
- Test: `tests/Feature/Backend/AuthenticationTest.php`

- [ ] **Step 1: Write the failing tests**

```php
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
```

Note: this checks the registered `RateLimiter::for('login', ...)` configuration directly instead of tripping the limiter over HTTP. An HTTP-loop version (POST 6 times, assert 429) would need to guess Laravel's internal cache key for a named-limiter-through-middleware, which isn't the same format as the `RateLimiter::clear('key')` example in the docs (that example is for the *manual* `RateLimiter::attempt()` API, a different code path) — guessing wrong would make the test either always pass for the wrong reason or randomly fail depending on cache state left over from other tests in the same run. Asserting the registration directly is deterministic and still proves the throttle is wired to 5/minute.

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact tests/Feature/Backend/AuthenticationTest.php`
Expected: FAIL — `/admin/login` returns 404 (route doesn't exist yet).

- [ ] **Step 3: Add the login rate limiter**

```php
<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });
    }
}
```

- [ ] **Step 4: Create `AuthenticatedSessionController`**

```php
<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('backend.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Le credenziali fornite non corrispondono ai nostri archivi.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('backend.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('backend.login');
    }
}
```

- [ ] **Step 5: Create the login view**

```blade
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accedi — Gestione FitFrame</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container" style="max-width: 400px; margin-top: 100px;">
        <h3 class="mb-4">Accedi</h3>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('backend.login') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" name="remember" class="form-check-input" id="remember">
                <label class="form-check-label" for="remember">Ricordami</label>
            </div>

            <button type="submit" class="btn btn-primary w-100">Accedi</button>

            <a href="{{ route('backend.password.request') }}" class="d-block text-center mt-3 small">Password dimenticata?</a>
        </form>
    </div>
</body>
</html>
```

- [ ] **Step 6: Wire the routes**

In `routes/backend.php`, replace the two empty groups:

```php
<?php

use App\Http\Controllers\Backend\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('backend.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login');
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    });
});
```

Note: `test_guests_are_redirected_to_login` and `test_login_screen_can_be_rendered` will pass now, but `redirect()->intended(route('backend.dashboard'))` in the controller points at a route that doesn't exist until Task 7 — that's fine, the test only asserts a redirect to `/admin` (the URI, not the named route resolution timing), which works because `route('backend.dashboard')` will exist by the time this controller method actually executes in the full app (Task 7 is next). If you run this task in isolation before Task 7 exists, this specific assertion will fail with a `RouteNotFoundException` — apply Task 7's route addition (just the route line, not the whole task) or run Tasks 5–7 together before checking in isolation.

- [ ] **Step 7: Run tests to verify they pass**

Run: `php artisan test --compact tests/Feature/Backend/AuthenticationTest.php`
Expected: PASS for `login_screen_can_be_rendered`, `wrong_password`, `guests_are_redirected_to_login`, `rate_limited`. The `authenticate_with_correct_credentials` and `can_logout` tests need `backend.dashboard` to exist — complete Task 7's route addition first, then re-run this file to confirm all six pass together.

- [ ] **Step 8: Commit**

```bash
git add app/Http/Controllers/Backend/AuthenticatedSessionController.php app/Providers/AppServiceProvider.php resources/views/backend/auth/login.blade.php routes/backend.php tests/Feature/Backend/AuthenticationTest.php
git commit -m "feat: manual login/logout with rate-limited authentication"
```

---

### Task 6: Password reset by email (core Laravel, no package)

**Files:**
- Create: `app/Notifications/ResetPasswordNotification.php`
- Create: `app/Http/Controllers/Backend/PasswordResetLinkController.php`
- Create: `app/Http/Controllers/Backend/NewPasswordController.php`
- Create: `resources/views/backend/auth/forgot-password.blade.php`
- Create: `resources/views/backend/auth/reset-password.blade.php`
- Modify: `routes/backend.php`
- Test: `tests/Feature/Backend/PasswordResetTest.php`

- [ ] **Step 1: Write the failing tests**

```php
<?php

namespace Tests\Feature\Backend;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_forgot_password_screen_can_be_rendered(): void
    {
        $this->get('/admin/password/forgot')->assertOk();
    }

    public function test_reset_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->superAdmin()->create();

        $this->post('/admin/password/forgot', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->superAdmin()->create();
        $this->post('/admin/password/forgot', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function (ResetPasswordNotification $notification) {
            $this->get('/admin/password/reset/'.$notification->token)->assertOk();

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->superAdmin()->create();
        $this->post('/admin/password/forgot', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function (ResetPasswordNotification $notification) use ($user) {
            $response = $this->post('/admin/password/reset', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

            $response->assertRedirect('/admin/login');

            $this->assertTrue(Auth::attempt(['email' => $user->email, 'password' => 'new-password']));

            return true;
        });
    }
}
```

Add `use Illuminate\Support\Facades\Auth;` to the imports (needed by the last test).

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact tests/Feature/Backend/PasswordResetTest.php`
Expected: FAIL — `/admin/password/forgot` returns 404.

- [ ] **Step 3: Make `token` readable on the notification test helper**

The test reads `$notification->token` — add a public readonly property so `Notification::assertSentTo`'s callback can grab it (see Step 5 below for the class; this is just calling out that the property must be `public`, not `private`, unlike the earlier draft in the design doc).

- [ ] **Step 4: Create the reset notification**

```php
<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    public function __construct(public readonly string $token)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('backend.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('Reset Password — Gestione FitFrame')
            ->line('Hai ricevuto questa email perché abbiamo ricevuto una richiesta di reset password per il tuo account.')
            ->action('Reimposta Password', $url)
            ->line('Se non hai richiesto un reset della password, ignora questa email.');
    }
}
```

- [ ] **Step 5: Create `PasswordResetLinkController`**

```php
<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('backend.auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }
}
```

- [ ] **Step 6: Create `NewPasswordController`**

```php
<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function create(Request $request, string $token): View
    {
        return view('backend.auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('backend.login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}
```

- [ ] **Step 7: Create the forgot-password view**

```blade
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password dimenticata — Gestione FitFrame</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="d-flex align-items-center justify-content-center vh-100">
        <div class="card p-4" style="max-width: 400px; width: 100%;">
            <h3 class="mb-4">Password dimenticata</h3>

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('backend.password.email') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus autocomplete="username">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-100">Invia link di reset</button>
            </form>
        </div>
    </div>
</body>
</html>
```

Nota: usa le utility flex di Bootstrap (`d-flex`/`vh-100`) per centrare invece di `margin-top` fisso — stesso pattern corretto già applicato alla view di login (Task 5), coerente con la convenzione del progetto (utility Bootstrap invece di CSS/inline custom).

- [ ] **Step 8: Create the reset-password view**

```blade
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reimposta Password — Gestione FitFrame</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="d-flex align-items-center justify-content-center vh-100">
        <div class="card p-4" style="max-width: 400px; width: 100%;">
            <h3 class="mb-4">Reimposta Password</h3>

            <form method="POST" action="{{ route('backend.password.update') }}">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $email) }}" class="form-control @error('email') is-invalid @enderror" required autofocus autocomplete="username">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Nuova password</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Conferma password</label>
                    <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                </div>

                <button type="submit" class="btn btn-primary w-100">Reimposta Password</button>
            </form>
        </div>
    </div>
</body>
</html>
```

- [ ] **Step 9: Wire the routes**

Add to the `guest` group in `routes/backend.php`:

```php
Route::get('password/forgot', [PasswordResetLinkController::class, 'create'])->name('password.request');
Route::post('password/forgot', [PasswordResetLinkController::class, 'store'])->name('password.email');

Route::get('password/reset/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
Route::post('password/reset', [NewPasswordController::class, 'store'])->name('password.update');
```

Add the matching `use` imports at the top of the file.

- [ ] **Step 10: Run tests to verify they pass**

Run: `php artisan test --compact tests/Feature/Backend/PasswordResetTest.php`
Expected: PASS.

- [ ] **Step 11: Set the mail driver for local dev**

In `.env`, ensure `MAIL_MAILER=log` (so reset emails are written to `storage/logs/laravel.log` until real SMTP is configured for production). Testing already forces `MAIL_MAILER=array` via `phpunit.xml`, so this doesn't affect the test run above.

- [ ] **Step 12: Commit**

```bash
git add app/Notifications/ResetPasswordNotification.php app/Http/Controllers/Backend/PasswordResetLinkController.php app/Http/Controllers/Backend/NewPasswordController.php resources/views/backend/auth/forgot-password.blade.php resources/views/backend/auth/reset-password.blade.php routes/backend.php tests/Feature/Backend/PasswordResetTest.php
git commit -m "feat: password reset by email using core Laravel notifications"
```

---

### Task 7: Backend layout — collapsible aside, dark mode, dashboard

**Files:**
- Create: `app/Http/Controllers/Backend/DashboardController.php`
- Create: `resources/views/backend/layouts/app.blade.php`
- Create: `resources/views/backend/dashboard.blade.php`
- Create: `public/css/backend.css`
- Create: `public/js/backend.js`
- Modify: `routes/backend.php`
- Test: `tests/Feature/Backend/DashboardTest.php`

Behavior being built (see spec §E.1): the aside starts expanded, a
chevron button collapses it to icon-only, collapsed+hovering shows a
floating full-width preview (CSS `:hover` only — never touches the
persisted state), and a light/dark switch lives in a top navbar above
the main content. Both the collapsed flag and the theme choice persist
across page loads via `localStorage` (this is a multi-page Blade app,
not an SPA, so there's no client-side router to hold that state
in-memory between requests). The aside itself always stays dark navy —
only the main content responds to the light/dark switch, via
Bootstrap 5.3's native `data-bs-theme` attribute.

- [ ] **Step 1: Write the failing tests**

```php
<?php

namespace Tests\Feature\Backend;

use App\Models\Gym;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_super_admin_sees_platform_menu(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get('/admin');

        $response->assertOk();
        $response->assertSee('Strutture');
        $response->assertSee('Utenti');
        $response->assertSee('Super Admin');
    }

    public function test_gym_admin_does_not_see_platform_menu(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();

        $response = $this->actingAs($gymAdmin)->get('/admin');

        $response->assertOk();
        $response->assertDontSee('Strutture');
        $response->assertDontSee('Utenti');
        $response->assertDontSee('Super Admin');
    }

    public function test_layout_ships_the_sidebar_toggle_and_theme_switch(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get('/admin');

        $response->assertOk();
        $response->assertSee('id="sidebar-toggle"', false);
        $response->assertSee('id="theme-toggle"', false);
        $response->assertSee('backend.css', false);
        $response->assertSee('backend.js', false);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact tests/Feature/Backend/DashboardTest.php`
Expected: FAIL — `/admin` returns 404.

- [ ] **Step 3: Create `DashboardController`**

```php
<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('backend.dashboard');
    }
}
```

- [ ] **Step 4: Create `public/css/backend.css`**

```css
:root {
    --backend-sidebar-width: 260px;
    --backend-sidebar-collapsed-width: 72px;
}

.backend-layout {
    position: relative;
}

.backend-aside {
    width: var(--backend-sidebar-width);
    flex-shrink: 0;
    min-height: 100vh;
    transition: width .15s ease;
    position: relative;
    z-index: 1020;
    overflow: hidden;
}

html.sidebar-collapsed .backend-aside {
    width: var(--backend-sidebar-collapsed-width);
}

/* Da collassato, l'hover espande sopra il contenuto senza spostarlo:
   position passa ad absolute solo qui, non nello stato base. */
html.sidebar-collapsed .backend-aside:hover {
    width: var(--backend-sidebar-width);
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    overflow: visible;
    box-shadow: 4px 0 16px rgba(0, 0, 0, .35);
}

html.sidebar-collapsed .backend-aside .backend-nav-label,
html.sidebar-collapsed .backend-aside .backend-brand-text,
html.sidebar-collapsed .backend-aside .backend-menu-heading {
    display: none;
}

html.sidebar-collapsed .backend-aside:hover .backend-nav-label,
html.sidebar-collapsed .backend-aside:hover .backend-brand-text,
html.sidebar-collapsed .backend-aside:hover .backend-menu-heading {
    display: inline;
}

#sidebar-toggle .fa-chevron-right {
    display: none;
}

html.sidebar-collapsed #sidebar-toggle .fa-chevron-left {
    display: none;
}

html.sidebar-collapsed #sidebar-toggle .fa-chevron-right {
    display: inline;
}
```

- [ ] **Step 5: Create `public/js/backend.js`**

```js
document.addEventListener('DOMContentLoaded', function () {
    var sidebarToggle = document.getElementById('sidebar-toggle');
    var themeToggle = document.getElementById('theme-toggle');

    sidebarToggle.addEventListener('click', function () {
        var collapsed = document.documentElement.classList.toggle('sidebar-collapsed');
        localStorage.setItem('fitframe_admin_sidebar_collapsed', collapsed ? '1' : '0');
    });

    themeToggle.checked = document.documentElement.getAttribute('data-bs-theme') === 'dark';

    themeToggle.addEventListener('change', function () {
        var theme = this.checked ? 'dark' : 'light';
        document.documentElement.setAttribute('data-bs-theme', theme);
        localStorage.setItem('fitframe_admin_theme', theme);
    });
});
```

- [ ] **Step 6: Create the backend layout**

```blade
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Gestione FitFrame')</title>

    {{--
        Applica tema e stato della sidebar PRIMA del paint, leggendo
        localStorage — evita un flash dello stato sbagliato (chiaro poi
        scuro, espanso poi collassato) al caricamento della pagina.
    --}}
    <script>
        (function () {
            var theme = localStorage.getItem('fitframe_admin_theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', theme);

            if (localStorage.getItem('fitframe_admin_sidebar_collapsed') === '1') {
                document.documentElement.classList.add('sidebar-collapsed');
            }
        })();
    </script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/backend.css') }}?v={{ filemtime(public_path('css/backend.css')) }}">
</head>
<body>
    <div class="d-flex backend-layout">
        <aside class="backend-aside bg-dark text-white p-3">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="backend-brand-text mb-0">Gestione FitFrame</h5>
                <button type="button" id="sidebar-toggle" class="btn btn-sm btn-outline-light border-0">
                    <i class="fa-solid fa-chevron-left"></i>
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>

            <nav class="nav flex-column gap-1">
                @if (auth()->user()->role === \App\Enums\UserRole::SuperAdmin)
                    <a class="nav-link text-white" href="{{ route('backend.super-admin.index') }}">
                        <i class="fa-solid fa-user-shield fa-fw"></i>
                        <span class="backend-nav-label">Super Admin</span>
                    </a>
                    <a class="nav-link text-white" href="{{ route('backend.utenti.index') }}">
                        <i class="fa-solid fa-users fa-fw"></i>
                        <span class="backend-nav-label">Utenti</span>
                    </a>
                    <a class="nav-link text-white" href="{{ route('backend.strutture.index') }}">
                        <i class="fa-solid fa-building fa-fw"></i>
                        <span class="backend-nav-label">Strutture</span>
                    </a>
                @endif
            </nav>

            <form method="POST" action="{{ route('backend.logout') }}" class="mt-4">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm w-100">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span class="backend-nav-label">Esci</span>
                </button>
            </form>
        </aside>

        <div class="flex-grow-1 d-flex flex-column">
            <nav class="navbar bg-body border-bottom px-3">
                <span class="navbar-text">@yield('title', 'Dashboard')</span>

                <div class="d-flex align-items-center gap-2 ms-auto">
                    <i class="fa-solid fa-sun"></i>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" role="switch" id="theme-toggle">
                    </div>
                    <i class="fa-solid fa-moon"></i>
                </div>
            </nav>

            <main class="flex-grow-1 p-4">
                @impersonating
                    <div class="alert alert-warning d-flex justify-content-between align-items-center">
                        <span>Stai impersonando {{ auth()->user()->name }}.</span>
                        <a href="{{ route('backend.impersonate.leave') }}" class="btn btn-sm btn-dark">Torna al tuo account</a>
                    </div>
                @endImpersonating

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/backend.js') }}?v={{ filemtime(public_path('js/backend.js')) }}"></script>
</body>
</html>
```

Note: `@impersonating`/`@endImpersonating` are registered by `lab404/laravel-impersonate` (Task 1) — they compile to `is_impersonating()` checks, safe to use even before any impersonation route exists (it just evaluates false).

- [ ] **Step 7: Create the dashboard view**

```blade
@extends('backend.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h1 class="h3">Ciao, {{ auth()->user()->name }}</h1>
    <p class="text-muted">Sezione contenuti in arrivo.</p>
@endsection
```

- [ ] **Step 8: Wire the route**

Add to the `auth` group in `routes/backend.php`:

```php
Route::get('/', DashboardController::class)->name('dashboard');
```

Add `use App\Http\Controllers\Backend\DashboardController;` at the top.

- [ ] **Step 9: Run tests to verify they pass**

Run: `php artisan test --compact tests/Feature/Backend/DashboardTest.php`
Expected: `test_layout_ships_the_sidebar_toggle_and_theme_switch` PASSes now. The two menu-visibility tests still FAIL — they reference `backend.super-admin.index`, `backend.utenti.index`, `backend.strutture.index`, which don't exist until Tasks 8–10. **This is expected at this point in the plan.** Continue straight to Task 8 and come back to run this file once all three resource controllers exist.

- [ ] **Step 10: Also re-run Task 5's `AuthenticationTest.php` now that `backend.dashboard` exists**

Run: `php artisan test --compact tests/Feature/Backend/AuthenticationTest.php`
Expected: all tests PASS now.

- [ ] **Step 11: Manual browser check (PHPUnit can't drive real hover/click/localStorage)**

Run `php artisan serve`, log in as a super_admin, and confirm in the browser:
- Chevron collapses the aside to icon-only; clicking again re-expands it.
- Hovering the collapsed aside shows the full menu as an overlay; moving the mouse away collapses it again without needing another click.
- Reloading the page (or navigating to another `/admin/*` page) keeps the collapsed/expanded state — no flash of the wrong state.
- The light/dark switch flips the main content area between themes instantly, survives a page reload, and the aside stays dark navy in both.

- [ ] **Step 12: Commit**

```bash
git add app/Http/Controllers/Backend/DashboardController.php resources/views/backend/layouts/app.blade.php resources/views/backend/dashboard.blade.php public/css/backend.css public/js/backend.js routes/backend.php tests/Feature/Backend/DashboardTest.php
git commit -m "feat: collapsible aside + dark mode toggle for the backend layout"
```

---

### Task 8: Strutture (Gym) CRUD

**Files:**
- Create: `app/Http/Requests/Backend/StoreGymRequest.php`
- Create: `app/Http/Requests/Backend/UpdateGymRequest.php`
- Create: `app/Http/Controllers/Backend/GymController.php`
- Create: `resources/views/backend/strutture/{index,create,edit,_form}.blade.php`
- Modify: `routes/backend.php`
- Test: `tests/Feature/Backend/GymControllerTest.php`

- [ ] **Step 1: Write the failing tests**

```php
<?php

namespace Tests\Feature\Backend;

use App\Models\Domain;
use App\Models\Gym;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class GymControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_gym_admin_cannot_view_strutture_list(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();

        $this->actingAs($gymAdmin)->get('/admin/strutture')->assertForbidden();
    }

    public function test_super_admin_can_create_a_gym_with_its_domain(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->post('/admin/strutture', [
            'name' => 'Nuova Palestra',
            'slug' => 'pulse',
            'domain' => 'nuovapalestra.test',
        ]);

        $response->assertRedirect('/admin/strutture');
        $this->assertDatabaseHas('gyms', ['name' => 'Nuova Palestra', 'slug' => 'pulse']);
        $this->assertDatabaseHas('domains', ['domain' => 'nuovapalestra.test']);
    }

    public function test_slug_must_be_an_installed_theme(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->post('/admin/strutture', [
            'name' => 'Nuova Palestra',
            'slug' => 'non-esiste',
            'domain' => 'nuovapalestra.test',
        ]);

        $response->assertSessionHasErrors('slug');
    }

    public function test_super_admin_can_update_a_gym(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $gym = Gym::factory()->create(['slug' => 'pulse']);
        $domain = Domain::factory()->for($gym)->create(['domain' => 'vecchio.test']);

        $response = $this->actingAs($superAdmin)->put("/admin/strutture/{$gym->id}", [
            'name' => 'Nome Aggiornato',
            'slug' => 'zenflow',
            'domain' => 'nuovo.test',
        ]);

        $response->assertRedirect('/admin/strutture');
        $this->assertDatabaseHas('gyms', ['id' => $gym->id, 'name' => 'Nome Aggiornato', 'slug' => 'zenflow']);
        $this->assertDatabaseHas('domains', ['id' => $domain->id, 'domain' => 'nuovo.test']);
    }

    public function test_super_admin_cannot_delete_a_gym_with_assigned_admins(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $gym = Gym::factory()->create();
        User::factory()->for($gym)->create();

        $response = $this->actingAs($superAdmin)->delete("/admin/strutture/{$gym->id}");

        $response->assertRedirect('/admin/strutture');
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('gyms', ['id' => $gym->id]);
    }

    public function test_super_admin_can_delete_a_gym_without_admins(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $gym = Gym::factory()->create();

        $response = $this->actingAs($superAdmin)->delete("/admin/strutture/{$gym->id}");

        $response->assertRedirect('/admin/strutture');
        $this->assertDatabaseMissing('gyms', ['id' => $gym->id]);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact tests/Feature/Backend/GymControllerTest.php`
Expected: FAIL — `/admin/strutture` returns 404.

- [ ] **Step 3: Create `StoreGymRequest`**

```php
<?php

namespace App\Http\Requests\Backend;

use App\Models\Gym;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGymRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Gym::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', Rule::in(Gym::availableThemes())],
            'domain' => ['required', 'string', 'max:255', 'unique:domains,domain'],
        ];
    }
}
```

- [ ] **Step 4: Create `UpdateGymRequest`**

```php
<?php

namespace App\Http\Requests\Backend;

use App\Models\Gym;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGymRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('gym'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Gym $gym */
        $gym = $this->route('gym');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', Rule::in(Gym::availableThemes())],
            'domain' => [
                'required', 'string', 'max:255',
                Rule::unique('domains', 'domain')->ignore($gym->domains->first()?->id),
            ],
        ];
    }
}
```

- [ ] **Step 5: Create `GymController`**

```php
<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\StoreGymRequest;
use App\Http\Requests\Backend\UpdateGymRequest;
use App\Models\Gym;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class GymController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Gym::class);

        return view('backend.strutture.index', [
            'gyms' => Gym::with('domains')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Gym::class);

        return view('backend.strutture.create', [
            'themes' => Gym::availableThemes(),
        ]);
    }

    public function store(StoreGymRequest $request): RedirectResponse
    {
        $gym = Gym::create($request->safe()->only(['name', 'slug']));

        $gym->domains()->create(['domain' => $request->validated('domain')]);

        return redirect()->route('backend.strutture.index')->with('success', 'Struttura creata con successo.');
    }

    public function edit(Gym $gym): View
    {
        Gate::authorize('update', $gym);

        return view('backend.strutture.edit', [
            'gym' => $gym->load('domains'),
            'themes' => Gym::availableThemes(),
        ]);
    }

    public function update(UpdateGymRequest $request, Gym $gym): RedirectResponse
    {
        $gym->update($request->safe()->only(['name', 'slug']));

        $domain = $gym->domains->first();

        if ($domain) {
            $domain->update(['domain' => $request->validated('domain')]);
        } else {
            $gym->domains()->create(['domain' => $request->validated('domain')]);
        }

        return redirect()->route('backend.strutture.index')->with('success', 'Struttura aggiornata con successo.');
    }

    public function destroy(Gym $gym): RedirectResponse
    {
        Gate::authorize('delete', $gym);

        if ($gym->admins()->exists()) {
            return redirect()->route('backend.strutture.index')
                ->with('error', 'Impossibile eliminare: la struttura ha ancora utenti assegnati.');
        }

        $gym->delete();

        return redirect()->route('backend.strutture.index')->with('success', 'Struttura eliminata con successo.');
    }
}
```

- [ ] **Step 6: Create the shared form partial**

```blade
<div class="mb-3">
    <label class="form-label">Nome</label>
    <input type="text" name="name" value="{{ old('name', $gym->name ?? '') }}" class="form-control @error('name') is-invalid @enderror">
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Tema</label>
    <select name="slug" class="form-select @error('slug') is-invalid @enderror">
        <option value="">— Seleziona —</option>
        @foreach ($themes as $theme)
            <option value="{{ $theme }}" @selected(old('slug', $gym->slug ?? '') === $theme)>{{ $theme }}</option>
        @endforeach
    </select>
    @error('slug')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Dominio</label>
    <input type="text" name="domain" value="{{ old('domain', $gym->domains->first()->domain ?? '') }}" class="form-control @error('domain') is-invalid @enderror" placeholder="esempio.test">
    @error('domain')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
```

- [ ] **Step 7: Create the index view**

```blade
@extends('backend.layouts.app')

@section('title', 'Strutture')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Strutture</h1>
        <a href="{{ route('backend.strutture.create') }}" class="btn btn-primary">Nuova Struttura</a>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Tema</th>
                <th>Dominio</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($gyms as $gym)
                <tr>
                    <td>{{ $gym->name }}</td>
                    <td>{{ $gym->slug }}</td>
                    <td>{{ $gym->domains->first()?->domain }}</td>
                    <td class="text-end">
                        <a href="{{ route('backend.strutture.edit', $gym) }}" class="btn btn-sm btn-outline-secondary">Modifica</a>
                        <form method="POST" action="{{ route('backend.strutture.destroy', $gym) }}" class="d-inline" onsubmit="return confirm('Eliminare questa struttura?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Elimina</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
```

- [ ] **Step 8: Create the create/edit views**

`resources/views/backend/strutture/create.blade.php`:
```blade
@extends('backend.layouts.app')

@section('title', 'Nuova Struttura')

@section('content')
    <h1 class="h3 mb-4">Nuova Struttura</h1>

    <form method="POST" action="{{ route('backend.strutture.store') }}">
        @csrf
        @include('backend.strutture._form')

        <button type="submit" class="btn btn-primary">Crea</button>
    </form>
@endsection
```

`resources/views/backend/strutture/edit.blade.php`:
```blade
@extends('backend.layouts.app')

@section('title', 'Modifica Struttura')

@section('content')
    <h1 class="h3 mb-4">Modifica Struttura</h1>

    <form method="POST" action="{{ route('backend.strutture.update', $gym) }}">
        @csrf
        @method('PUT')
        @include('backend.strutture._form')

        <button type="submit" class="btn btn-primary">Salva</button>
    </form>
@endsection
```

- [ ] **Step 9: Wire the routes**

Add to the `auth` group in `routes/backend.php`:

```php
Route::resource('strutture', GymController::class)->except('show')->parameters(['strutture' => 'gym']);
```

Add `use App\Http\Controllers\Backend\GymController;` at the top.

- [ ] **Step 10: Run tests to verify they pass**

Run: `php artisan test --compact tests/Feature/Backend/GymControllerTest.php`
Expected: PASS.

- [ ] **Step 11: Run the full suite**

Run: `php artisan test --compact`
Expected: all PASS (`DashboardTest`'s menu assertions for `Strutture` now resolve).

- [ ] **Step 12: Run Pint**

Run: `vendor/bin/pint --dirty --format agent`
Expected: no errors, style fixed if any.

- [ ] **Step 13: Commit**

```bash
git add app/Http/Requests/Backend/StoreGymRequest.php app/Http/Requests/Backend/UpdateGymRequest.php app/Http/Controllers/Backend/GymController.php resources/views/backend/strutture routes/backend.php tests/Feature/Backend/GymControllerTest.php
git commit -m "feat: Strutture (Gym) CRUD for super_admin"
```

---

### Task 9: Utenti (gym_admin) CRUD + impersonation wiring

**Files:**
- Create: `app/Http/Requests/Backend/StoreGymAdminRequest.php`
- Create: `app/Http/Requests/Backend/UpdateGymAdminRequest.php`
- Create: `app/Http/Controllers/Backend/GymAdminController.php`
- Create: `resources/views/backend/utenti/{index,create,edit,_form}.blade.php`
- Modify: `routes/backend.php`
- Test: `tests/Feature/Backend/GymAdminControllerTest.php`
- Test: `tests/Feature/Backend/ImpersonationTest.php`

- [ ] **Step 1: Write the failing tests for the CRUD**

```php
<?php

namespace Tests\Feature\Backend;

use App\Enums\UserRole;
use App\Models\Gym;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class GymAdminControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_gym_admin_cannot_view_utenti_list(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();

        $this->actingAs($gymAdmin)->get('/admin/utenti')->assertForbidden();
    }

    public function test_super_admin_can_create_a_gym_admin(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $gym = Gym::factory()->create();

        $response = $this->actingAs($superAdmin)->post('/admin/utenti', [
            'name' => 'Mario Rossi',
            'email' => 'mario@example.test',
            'password' => 'password123',
            'gym_id' => $gym->id,
        ]);

        $response->assertRedirect('/admin/utenti');
        $this->assertDatabaseHas('users', [
            'email' => 'mario@example.test',
            'role' => UserRole::GymAdmin,
            'gym_id' => $gym->id,
        ]);
    }

    public function test_a_gym_can_have_more_than_one_admin(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $gym = Gym::factory()->create();
        User::factory()->for($gym)->create();

        $response = $this->actingAs($superAdmin)->post('/admin/utenti', [
            'name' => 'Secondo Admin',
            'email' => 'secondo@example.test',
            'password' => 'password123',
            'gym_id' => $gym->id,
        ]);

        $response->assertRedirect('/admin/utenti');
        $this->assertSame(2, $gym->admins()->count());
    }

    public function test_updating_without_a_password_keeps_the_old_one(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        $originalHash = $gymAdmin->password;

        $response = $this->actingAs($superAdmin)->put("/admin/utenti/{$gymAdmin->id}", [
            'name' => 'Nome Cambiato',
            'email' => $gymAdmin->email,
            'gym_id' => $gym->id,
        ]);

        $response->assertRedirect('/admin/utenti');
        $this->assertSame($originalHash, $gymAdmin->fresh()->password);
    }

    public function test_the_super_admin_route_returns_404_for_a_gym_admin_id(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();

        $this->actingAs($superAdmin)->get("/admin/super-admin/{$gymAdmin->id}/edit")->assertNotFound();
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact tests/Feature/Backend/GymAdminControllerTest.php`
Expected: FAIL — `/admin/utenti` returns 404.

- [ ] **Step 3: Create `StoreGymAdminRequest`**

```php
<?php

namespace App\Http\Requests\Backend;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreGymAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::defaults()],
            'gym_id' => ['required', 'integer', 'exists:gyms,id'],
        ];
    }
}
```

- [ ] **Step 4: Create `UpdateGymAdminRequest`**

```php
<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateGymAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('user'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'password' => ['nullable', 'string', Password::defaults()],
            'gym_id' => ['required', 'integer', 'exists:gyms,id'],
        ];
    }
}
```

- [ ] **Step 5: Create `GymAdminController`**

```php
<?php

namespace App\Http\Controllers\Backend;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\StoreGymAdminRequest;
use App\Http\Requests\Backend\UpdateGymAdminRequest;
use App\Models\Gym;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class GymAdminController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', User::class);

        return view('backend.utenti.index', [
            'users' => User::with('gym')->where('role', UserRole::GymAdmin)->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', User::class);

        return view('backend.utenti.create', [
            'gyms' => Gym::orderBy('name')->get(),
        ]);
    }

    public function store(StoreGymAdminRequest $request): RedirectResponse
    {
        User::create([
            ...$request->safe()->only(['name', 'email', 'gym_id']),
            'password' => Hash::make($request->validated('password')),
            'role' => UserRole::GymAdmin,
        ]);

        return redirect()->route('backend.utenti.index')->with('success', 'Utente creato con successo.');
    }

    public function edit(User $user): View
    {
        abort_unless($user->role === UserRole::GymAdmin, 404);
        Gate::authorize('update', $user);

        return view('backend.utenti.edit', [
            'user' => $user,
            'gyms' => Gym::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateGymAdminRequest $request, User $user): RedirectResponse
    {
        abort_unless($user->role === UserRole::GymAdmin, 404);

        $data = $request->safe()->only(['name', 'email', 'gym_id']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->validated('password'));
        }

        $user->update($data);

        return redirect()->route('backend.utenti.index')->with('success', 'Utente aggiornato con successo.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless($user->role === UserRole::GymAdmin, 404);
        Gate::authorize('delete', $user);

        $user->delete();

        return redirect()->route('backend.utenti.index')->with('success', 'Utente eliminato con successo.');
    }
}
```

- [ ] **Step 6: Create the shared form partial**

```blade
<div class="mb-3">
    <label class="form-label">Nome</label>
    <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" class="form-control @error('name') is-invalid @enderror">
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Email</label>
    <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" class="form-control @error('email') is-invalid @enderror">
    @error('email')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Password{{ isset($user) ? ' (lascia vuoto per non cambiarla)' : '' }}</label>
    <input type="password" name="password" value="{{ old('password', isset($user) ? '' : '12345678') }}" class="form-control @error('password') is-invalid @enderror">
    @error('password')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Struttura</label>
    <select name="gym_id" class="form-select @error('gym_id') is-invalid @enderror">
        <option value="">— Seleziona —</option>
        @foreach ($gyms as $gym)
            <option value="{{ $gym->id }}" @selected((int) old('gym_id', $user->gym_id ?? '') === $gym->id)>{{ $gym->name }}</option>
        @endforeach
    </select>
    @error('gym_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
```

- [ ] **Step 7: Create the index view (with the impersonate button)**

```blade
@extends('backend.layouts.app')

@section('title', 'Utenti')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Utenti</h1>
        <a href="{{ route('backend.utenti.create') }}" class="btn btn-primary">Nuovo Utente</a>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Email</th>
                <th>Struttura</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->gym?->name }}</td>
                    <td class="text-end">
                        @canBeImpersonated($user)
                            <a href="{{ route('backend.impersonate', $user->id) }}" class="btn btn-sm btn-outline-primary">Impersona</a>
                        @endCanBeImpersonated
                        <a href="{{ route('backend.utenti.edit', $user) }}" class="btn btn-sm btn-outline-secondary">Modifica</a>
                        <form method="POST" action="{{ route('backend.utenti.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Eliminare questo utente?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Elimina</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
```

- [ ] **Step 8: Create the create/edit views**

`resources/views/backend/utenti/create.blade.php`:
```blade
@extends('backend.layouts.app')

@section('title', 'Nuovo Utente')

@section('content')
    <h1 class="h3 mb-4">Nuovo Utente</h1>

    <form method="POST" action="{{ route('backend.utenti.store') }}">
        @csrf
        @include('backend.utenti._form')

        <button type="submit" class="btn btn-primary">Crea</button>
    </form>
@endsection
```

`resources/views/backend/utenti/edit.blade.php`:
```blade
@extends('backend.layouts.app')

@section('title', 'Modifica Utente')

@section('content')
    <h1 class="h3 mb-4">Modifica Utente</h1>

    <form method="POST" action="{{ route('backend.utenti.update', $user) }}">
        @csrf
        @method('PUT')
        @include('backend.utenti._form')

        <button type="submit" class="btn btn-primary">Salva</button>
    </form>
@endsection
```

- [ ] **Step 9: Wire the routes, including `Route::impersonate()`**

Add to the `auth` group in `routes/backend.php`:

```php
Route::resource('utenti', GymAdminController::class)->except('show')->parameters(['utenti' => 'user']);

Route::impersonate();
```

Add `use App\Http\Controllers\Backend\GymAdminController;` at the top. `Route::impersonate()` registers `backend.impersonate` (`GET /admin/impersonate/take/{id}/{guardName?}`) and `backend.impersonate.leave` (`GET /admin/impersonate/leave`) — named with the `backend.` prefix because it's declared inside this file's `Route::prefix('admin')->name('backend.')` group.

- [ ] **Step 10: Run the CRUD tests**

Run: `php artisan test --compact tests/Feature/Backend/GymAdminControllerTest.php`
Expected: PASS.

- [ ] **Step 11: Write the failing impersonation test**

```php
<?php

namespace Tests\Feature\Backend;

use App\Models\Gym;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ImpersonationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_super_admin_can_impersonate_a_gym_admin(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();

        $this->actingAs($superAdmin)
            ->get(route('backend.impersonate', $gymAdmin->id))
            ->assertRedirect();

        $this->assertAuthenticatedAs($gymAdmin);
    }

    public function test_leaving_impersonation_restores_the_super_admin(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();

        $this->actingAs($superAdmin)->get(route('backend.impersonate', $gymAdmin->id));
        $this->get(route('backend.impersonate.leave'));

        $this->assertAuthenticatedAs($superAdmin);
    }

    public function test_a_gym_admin_cannot_impersonate_anyone(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        $anotherGymAdmin = User::factory()->for($gym)->create();

        $this->actingAs($gymAdmin)
            ->get(route('backend.impersonate', $anotherGymAdmin->id))
            ->assertForbidden();
    }

    public function test_a_super_admin_cannot_impersonate_another_super_admin(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $anotherSuperAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->get(route('backend.impersonate', $anotherSuperAdmin->id))
            ->assertRedirect();

        $this->assertAuthenticatedAs($superAdmin);
    }
}
```

Nota: `lab404/laravel-impersonate`'s `ImpersonateController::take()` only `abort(403)`s per un rifiuto lato *attore* (`canImpersonate()` falso). Per un rifiuto lato *target* (`canBeImpersonated()` falso, questo caso), il pacchetto fa semplicemente `redirect()->back()` (302) senza impersonare — nessun buco di sicurezza (`$manager->take()` non viene mai chiamato), solo uno status code diverso da quello che ci si aspetterebbe. Il test verifica la cosa che conta davvero: `assertAuthenticatedAs($superAdmin)` conferma che l'impersonazione non è avvenuta, indipendentemente dallo status HTTP scelto dal pacchetto.

- [ ] **Step 12: Run test to verify current behavior**

Run: `php artisan test --compact tests/Feature/Backend/ImpersonationTest.php`
Expected: PASS — everything needed (`canImpersonate`/`canBeImpersonated` on `User`, the route, the trait) was already wired in Tasks 2 and this task's Step 9. If any test fails, check that `Lab404\Impersonate\Models\Impersonate` is on `User` (Task 2, Step 7) and that `Route::impersonate()` is inside the `auth` middleware group (Step 9 above).

- [ ] **Step 13: Run the full suite**

Run: `php artisan test --compact`
Expected: all PASS, including `DashboardTest`'s `Utenti` menu assertion.

- [ ] **Step 14: Run Pint**

Run: `vendor/bin/pint --dirty --format agent`

- [ ] **Step 15: Commit**

```bash
git add app/Http/Requests/Backend/StoreGymAdminRequest.php app/Http/Requests/Backend/UpdateGymAdminRequest.php app/Http/Controllers/Backend/GymAdminController.php resources/views/backend/utenti routes/backend.php tests/Feature/Backend/GymAdminControllerTest.php tests/Feature/Backend/ImpersonationTest.php
git commit -m "feat: Utenti (gym_admin) CRUD with impersonation"
```

---

### Task 10: Super Admin CRUD

**Files:**
- Modify: `app/Policies/UserPolicy.php`
- Create: `app/Http/Requests/Backend/StoreSuperAdminRequest.php`
- Create: `app/Http/Requests/Backend/UpdateSuperAdminRequest.php`
- Create: `app/Http/Controllers/Backend/SuperAdminController.php`
- Create: `resources/views/backend/super-admin/{index,create,edit,_form}.blade.php`
- Modify: `routes/backend.php`
- Test: `tests/Unit/Policies/UserPolicyTest.php`
- Test: `tests/Feature/Backend/SuperAdminControllerTest.php`

**Important prerequisite fixed by this task:** Task 9's code review hardened `UserPolicy::update()`/`delete()` to require `$target->role === UserRole::GymAdmin` (closing a gap where the "never touch a super_admin via the gym-admin routes" guarantee rested only on controller-level guards). Those two abilities are now explicitly GymAdmin-target-only — reusing them here for super_admin-on-super_admin management would always return `false`. This task adds two dedicated abilities, `updateSuperAdmin`/`deleteSuperAdmin`, instead of reusing `update`/`delete`.

- [ ] **Step 1: Write the failing tests for the new Policy abilities**

```php
// Aggiungi a tests/Unit/Policies/UserPolicyTest.php (in fondo alla classe esistente)

public function test_super_admin_can_manage_another_super_admin_via_dedicated_ability(): void
{
    $superAdmin = User::factory()->superAdmin()->create();
    $otherSuperAdmin = User::factory()->superAdmin()->create();

    $this->assertTrue($this->policy->updateSuperAdmin($superAdmin, $otherSuperAdmin));
    $this->assertTrue($this->policy->deleteSuperAdmin($superAdmin, $otherSuperAdmin));
}

public function test_super_admin_cannot_delete_themselves_via_dedicated_ability(): void
{
    $superAdmin = User::factory()->superAdmin()->create();

    $this->assertFalse($this->policy->deleteSuperAdmin($superAdmin, $superAdmin));
}

public function test_dedicated_super_admin_abilities_reject_a_gym_admin_target(): void
{
    $superAdmin = User::factory()->superAdmin()->create();
    $gymAdmin = User::factory()->create();

    $this->assertFalse($this->policy->updateSuperAdmin($superAdmin, $gymAdmin));
    $this->assertFalse($this->policy->deleteSuperAdmin($superAdmin, $gymAdmin));
}

public function test_gym_admin_cannot_use_dedicated_super_admin_abilities(): void
{
    $gymAdmin = User::factory()->create();
    $superAdmin = User::factory()->superAdmin()->create();

    $this->assertFalse($this->policy->updateSuperAdmin($gymAdmin, $superAdmin));
    $this->assertFalse($this->policy->deleteSuperAdmin($gymAdmin, $superAdmin));
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact tests/Unit/Policies/UserPolicyTest.php`
Expected: FAIL — `Call to undefined method App\Policies\UserPolicy::updateSuperAdmin()`.

- [ ] **Step 3: Add the two dedicated abilities to `UserPolicy`**

Add these two methods to the existing `app/Policies/UserPolicy.php` (alongside `update()`/`delete()`, which stay exactly as Task 9 left them — GymAdmin-target-only):

```php
    /**
     * Usata da SuperAdminController: gestione di un altro super_admin.
     */
    public function updateSuperAdmin(User $user, User $target): bool
    {
        return $user->role === UserRole::SuperAdmin && $target->role === UserRole::SuperAdmin;
    }

    public function deleteSuperAdmin(User $user, User $target): bool
    {
        return $user->role === UserRole::SuperAdmin
            && $target->role === UserRole::SuperAdmin
            && $user->id !== $target->id;
    }
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --compact tests/Unit/Policies/UserPolicyTest.php`
Expected: PASS (all tests in the file, old and new).

- [ ] **Step 5: Write the failing tests for the CRUD**

```php
<?php

namespace Tests\Feature\Backend;

use App\Enums\UserRole;
use App\Models\Gym;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SuperAdminControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_gym_admin_cannot_view_super_admin_list(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();

        $this->actingAs($gymAdmin)->get('/admin/super-admin')->assertForbidden();
    }

    public function test_super_admin_can_create_another_super_admin_without_a_gym(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->post('/admin/super-admin', [
            'name' => 'Nuovo Super',
            'email' => 'nuovosuper@example.test',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin/super-admin');
        $this->assertDatabaseHas('users', [
            'email' => 'nuovosuper@example.test',
            'role' => UserRole::SuperAdmin,
            'gym_id' => null,
        ]);
    }

    public function test_super_admin_can_update_another_super_admin(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $other = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->put("/admin/super-admin/{$other->id}", [
            'name' => 'Nome Cambiato',
            'email' => $other->email,
        ]);

        $response->assertRedirect('/admin/super-admin');
        $this->assertDatabaseHas('users', ['id' => $other->id, 'name' => 'Nome Cambiato']);
    }

    public function test_super_admin_cannot_delete_themselves(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->delete("/admin/super-admin/{$superAdmin->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $superAdmin->id]);
    }

    public function test_super_admin_can_delete_another_super_admin(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $other = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->delete("/admin/super-admin/{$other->id}");

        $response->assertRedirect('/admin/super-admin');
        $this->assertDatabaseMissing('users', ['id' => $other->id]);
    }

    public function test_the_utenti_route_returns_404_for_a_super_admin_id(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $other = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->get("/admin/utenti/{$other->id}/edit")->assertNotFound();
    }
}
```

- [ ] **Step 6: Run test to verify it fails**

Run: `php artisan test --compact tests/Feature/Backend/SuperAdminControllerTest.php`
Expected: FAIL — `/admin/super-admin` returns 404.

- [ ] **Step 7: Create `StoreSuperAdminRequest`**

```php
<?php

namespace App\Http\Requests\Backend;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreSuperAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::defaults()],
        ];
    }
}
```

- [ ] **Step 8: Create `UpdateSuperAdminRequest`**

```php
<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateSuperAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('updateSuperAdmin', $this->route('user'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'password' => ['nullable', 'string', Password::defaults()],
        ];
    }
}
```

Note: uses the dedicated `updateSuperAdmin` ability (Step 3), not the generic `update` — `UserPolicy::update()` is GymAdmin-target-only as of Task 9, and would always reject a super_admin target.

- [ ] **Step 9: Create `SuperAdminController`**

```php
<?php

namespace App\Http\Controllers\Backend;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\StoreSuperAdminRequest;
use App\Http\Requests\Backend\UpdateSuperAdminRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SuperAdminController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', User::class);

        return view('backend.super-admin.index', [
            'users' => User::where('role', UserRole::SuperAdmin)->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', User::class);

        return view('backend.super-admin.create');
    }

    public function store(StoreSuperAdminRequest $request): RedirectResponse
    {
        User::create([
            ...$request->safe()->only(['name', 'email']),
            'password' => Hash::make($request->validated('password')),
            'role' => UserRole::SuperAdmin,
        ]);

        return redirect()->route('backend.super-admin.index')->with('success', 'Super admin creato con successo.');
    }

    public function edit(User $user): View
    {
        abort_unless($user->role === UserRole::SuperAdmin, 404);
        Gate::authorize('updateSuperAdmin', $user);

        return view('backend.super-admin.edit', ['user' => $user]);
    }

    public function update(UpdateSuperAdminRequest $request, User $user): RedirectResponse
    {
        abort_unless($user->role === UserRole::SuperAdmin, 404);

        $data = $request->safe()->only(['name', 'email']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->validated('password'));
        }

        $user->update($data);

        return redirect()->route('backend.super-admin.index')->with('success', 'Super admin aggiornato con successo.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless($user->role === UserRole::SuperAdmin, 404);
        Gate::authorize('deleteSuperAdmin', $user);

        $user->delete();

        return redirect()->route('backend.super-admin.index')->with('success', 'Super admin eliminato con successo.');
    }
}
```

Note: `edit()`/`destroy()` run `abort_unless` before `Gate::authorize`, and have no FormRequest in front of them, so a GymAdmin id hitting these routes 404s. `update()` goes through `UpdateSuperAdminRequest::authorize()` first (resolved by Laravel before the controller body runs) — a GymAdmin id there gets rejected by `updateSuperAdmin`'s target-role check with a 403, not a 404. Same asymmetry already established and accepted in Task 9 for the mirror case (a super_admin id hitting the Utenti routes): both paths correctly block the action, they just differ in which HTTP status the blocking layer returns. Don't try to unify this — see Task 9's `GymAdminControllerTest::test_utenti_routes_404_for_a_super_admin_id` for the precedent.

- [ ] **Step 10: Create the shared form partial**

```blade
<div class="mb-3">
    <label class="form-label">Nome</label>
    <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" class="form-control @error('name') is-invalid @enderror">
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Email</label>
    <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" class="form-control @error('email') is-invalid @enderror">
    @error('email')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Password{{ isset($user) ? ' (lascia vuoto per non cambiarla)' : '' }}</label>
    <input type="password" name="password" value="{{ old('password', isset($user) ? '' : '12345678') }}" class="form-control @error('password') is-invalid @enderror">
    @error('password')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
```

- [ ] **Step 11: Create the index view**

```blade
@extends('backend.layouts.app')

@section('title', 'Super Admin')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Super Admin</h1>
        <a href="{{ route('backend.super-admin.create') }}" class="btn btn-primary">Nuovo Super Admin</a>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Email</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td class="text-end">
                        <a href="{{ route('backend.super-admin.edit', $user) }}" class="btn btn-sm btn-outline-secondary">Modifica</a>
                        @if ($user->id !== auth()->id())
                            <form method="POST" action="{{ route('backend.super-admin.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Eliminare questo super admin?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Elimina</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
```

- [ ] **Step 12: Create the create/edit views**

`resources/views/backend/super-admin/create.blade.php`:
```blade
@extends('backend.layouts.app')

@section('title', 'Nuovo Super Admin')

@section('content')
    <h1 class="h3 mb-4">Nuovo Super Admin</h1>

    <form method="POST" action="{{ route('backend.super-admin.store') }}">
        @csrf
        @include('backend.super-admin._form')

        <button type="submit" class="btn btn-primary">Crea</button>
    </form>
@endsection
```

`resources/views/backend/super-admin/edit.blade.php`:
```blade
@extends('backend.layouts.app')

@section('title', 'Modifica Super Admin')

@section('content')
    <h1 class="h3 mb-4">Modifica Super Admin</h1>

    <form method="POST" action="{{ route('backend.super-admin.update', $user) }}">
        @csrf
        @method('PUT')
        @include('backend.super-admin._form')

        <button type="submit" class="btn btn-primary">Salva</button>
    </form>
@endsection
```

- [ ] **Step 13: Wire the routes**

Add to the `auth` group in `routes/backend.php`:

```php
Route::resource('super-admin', SuperAdminController::class)->except('show')->parameters(['super-admin' => 'user']);
```

Add `use App\Http\Controllers\Backend\SuperAdminController;` at the top.

- [ ] **Step 14: Run tests to verify they pass**

Run: `php artisan test --compact tests/Feature/Backend/SuperAdminControllerTest.php`
Expected: PASS.

- [ ] **Step 15: Run the full suite (all tasks together)**

Run: `php artisan test --compact`
Expected: all PASS, including `DashboardTest`'s full menu assertions (`Strutture`, `Utenti`, `Super Admin` all now resolve).

- [ ] **Step 16: Run Pint**

Run: `vendor/bin/pint --dirty --format agent`

- [ ] **Step 17: Commit**

```bash
git add app/Policies/UserPolicy.php app/Http/Requests/Backend/StoreSuperAdminRequest.php app/Http/Requests/Backend/UpdateSuperAdminRequest.php app/Http/Controllers/Backend/SuperAdminController.php resources/views/backend/super-admin routes/backend.php tests/Unit/Policies/UserPolicyTest.php tests/Feature/Backend/SuperAdminControllerTest.php
git commit -m "feat: Super Admin CRUD"
```

---

## Final check

- [ ] Run `php artisan test --compact` once more — full green suite.
- [ ] Run `vendor/bin/pint --format agent` (whole project, not just `--dirty`) to catch anything missed.
- [ ] Manually visit `/admin/login`, log in as a seeded super_admin, click through Strutture → Utenti → Super Admin, create one of each, impersonate a gym_admin, leave impersonation, log out. (No seeder for a default super_admin exists yet — create one ad hoc via `php artisan tinker --execute 'App\Models\User::factory()->superAdmin()->create(["email" => "admin@fitframe.test", "password" => bcrypt("password")]);'` for this manual pass only; a proper seeder is out of scope for this plan, same as the spec's deferred items.)

## Out of scope (deferred to future specs, per the design doc)

- Contenuti per-sezione (16 content key), upload media hero, riordino/abilitazione `gym_sections`.
- Multi-dominio per palestra, invito utenti via email, deploy/DNS reale.
