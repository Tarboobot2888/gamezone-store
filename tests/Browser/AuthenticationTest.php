<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AuthenticationTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->type('email', 'test@example.com')
                    ->type('password', 'password')
                    ->press('Login')
                    ->assertPathIs('/dashboard')
                    ->assertSee('Dashboard');
        });
    }

    public function test_user_can_register()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                    ->type('name', 'Test User')
                    ->type('email', 'newuser@example.com')
                    ->type('password', 'password123')
                    ->type('password_confirmation', 'password123')
                    ->press('Register')
                    ->assertPathIs('/dashboard')
                    ->assertSee('Dashboard');
        });
    }

    public function test_user_can_logout()
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/dashboard')
                    ->click('@user-dropdown')
                    ->press('Logout')
                    ->assertPathIs('/')
                    ->assertSee('Login');
        });
    }

    public function test_guest_cannot_access_dashboard()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/dashboard')
                    ->assertPathIs('/login');
        });
    }

    public function test_language_switcher_works()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->click('@language-dropdown')
                    ->clickLink('العربية')
                    ->assertSee('الرئيسية'); // Arabic for "Home"
        });
    }

    public function test_theme_toggle_works()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->click('@theme-toggle')
                    ->assertAttribute('html', 'data-bs-theme', 'light');
        });
    }
}
