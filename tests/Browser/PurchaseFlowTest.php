<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use App\Models\User;
use App\Models\Game;
use App\Models\Service;
use App\Models\GameAccount;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PurchaseFlowTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_user_can_complete_topup_purchase()
    {
        $user = User::factory()->create();
        $game = Game::factory()->create();
        $service = Service::factory()->create(['game_id' => $game->id]);

        $this->browse(function (Browser $browser) use ($user, $service) {
            $browser->loginAs($user)
                    ->visit('/topup')
                    ->waitFor('.game-option')
                    ->click('.game-option:first-child')
                    ->waitFor('.service-option')
                    ->click('.service-option:first-child')
                    ->waitFor('input[name="player_id"]')
                    ->type('player_id', '123456789')
                    ->type('server', 'Global')
                    ->click('button[type="submit"]')
                    ->waitForLocation('/checkout/')
                    ->assertSee('Order Summary')
                    ->assertSee($service->name);
        });
    }

    public function test_user_can_purchase_game_account()
    {
        $user = User::factory()->create();
        $account = GameAccount::factory()->create();

        $this->browse(function (Browser $browser) use ($user, $account) {
            $browser->loginAs($user)
                    ->visit('/accounts')
                    ->waitFor('.account-card')
                    ->click('.account-card:first-child .btn-success')
                    ->waitForLocation('/account/')
                    ->assertSee($account->title)
                    ->click('button:contains("Buy Now")')
                    ->waitForLocation('/checkout/')
                    ->assertSee('Order Summary');
        });
    }

    public function test_user_can_add_items_to_cart()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();

        $this->browse(function (Browser $browser) use ($user, $service) {
            $browser->loginAs($user)
                    ->visit('/topup')
                    ->waitFor('.service-card')
                    ->click('.service-card:first-child .btn-primary')
                    ->waitForLocation('/topup?service=')
                    ->type('player_id', '123456789')
                    ->click('button:contains("Add to Cart")')
                    ->waitFor('.toast')
                    ->assertSee('added to cart')
                    ->visit('/cart')
                    ->assertSee($service->name)
                    ->assertSee('$' . number_format($service->price, 2));
        });
    }

    public function test_guest_user_redirected_to_login()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/dashboard')
                    ->assertPathIs('/login')
                    ->assertSee('Login to GameZone');
        });
    }
}
