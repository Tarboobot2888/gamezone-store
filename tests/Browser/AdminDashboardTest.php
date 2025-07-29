<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Order;
use App\Models\Game;
use App\Models\Service;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdminDashboardTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->artisan('db:seed', ['--class' => 'RoleAndPermissionSeeder']);
        
        $this->admin = User::factory()->create();
        $this->admin->assignRole('super-admin');
    }

    public function test_admin_can_access_dashboard()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                    ->visit('/admin')
                    ->assertSee('Admin Dashboard')
                    ->assertSee('Total Users')
                    ->assertSee('Total Orders')
                    ->assertSee('Total Revenue');
        });
    }

    public function test_admin_can_view_users_list()
    {
        User::factory()->count(5)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                    ->visit('/admin/users')
                    ->assertSee('Users Management')
                    ->assertSee('Add User')
                    ->assertElementsCount('tbody tr', 6); // 5 + admin
        });
    }

    public function test_admin_can_create_user()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                    ->visit('/admin/users')
                    ->click('[data-bs-target="#createUserModal"]')
                    ->waitFor('#createUserModal')
                    ->type('name', 'Test User')
                    ->type('email', 'test@example.com')
                    ->type('password', 'password123')
                    ->select('role', 'user')
                    ->check('email_verified')
                    ->press('Create User')
                    ->waitForText('User created successfully');
        });

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);
    }

    public function test_admin_can_view_orders()
    {
        $game = Game::factory()->create();
        $service = Service::factory()->create(['game_id' => $game->id]);
        Order::factory()->count(3)->create(['service_id' => $service->id]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                    ->visit('/admin/orders')
                    ->assertSee('Orders Management')
                    ->assertElementsCount('tbody tr', 3);
        });
    }

    public function test_admin_can_update_order_status()
    {
        $game = Game::factory()->create();
        $service = Service::factory()->create(['game_id' => $game->id]);
        $order = Order::factory()->create([
            'service_id' => $service->id,
            'status' => 'pending'
        ]);

        $this->browse(function (Browser $browser) use ($order) {
            $browser->loginAs($this->admin)
                    ->visit('/admin/orders')
                    ->click('.dropdown-toggle')
                    ->waitFor('.dropdown-menu')
                    ->click('.dropdown-item:contains("Completed")')
                    ->waitForText('Order status updated successfully');
        });

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'completed'
        ]);
    }

    public function test_regular_user_cannot_access_admin_dashboard()
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/admin')
                    ->assertSee('403')
                    ->assertSee('Unauthorized');
        });
    }

    public function test_admin_can_export_orders()
    {
        $game = Game::factory()->create();
        $service = Service::factory()->create(['game_id' => $game->id]);
        Order::factory()->count(5)->create(['service_id' => $service->id]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                    ->visit('/admin/orders')
                    ->click('button:contains("Export")')
                    ->pause(2000); // Wait for download to start
        });
    }

    public function test_admin_can_filter_orders()
    {
        $game = Game::factory()->create();
        $service = Service::factory()->create(['game_id' => $game->id]);
        
        Order::factory()->create([
            'service_id' => $service->id,
            'status' => 'pending'
        ]);
        
        Order::factory()->create([
            'service_id' => $service->id,
            'status' => 'completed'
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                    ->visit('/admin/orders')
                    ->select('status', 'pending')
                    ->press('Filter')
                    ->assertElementsCount('tbody tr', 1)
                    ->assertSee('Pending');
        });
    }
}
