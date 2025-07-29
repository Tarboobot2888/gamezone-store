<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Order;
use App\Models\Service;
use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_list_their_orders()
    {
        $game = Game::factory()->create();
        $service = Service::factory()->create(['game_id' => $game->id]);
        
        Order::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'service_id' => $service->id
        ]);

        // Create orders for another user
        Order::factory()->count(2)->create(['service_id' => $service->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/orders');

        $response->assertStatus(200)
                ->assertJsonCount(3, 'data');
    }

    public function test_user_can_view_their_order()
    {
        $game = Game::factory()->create();
        $service = Service::factory()->create(['game_id' => $game->id]);
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'service_id' => $service->id
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
                ->assertJsonPath('id', $order->id)
                ->assertJsonPath('order_number', $order->order_number);
    }

    public function test_user_cannot_view_other_users_order()
    {
        $game = Game::factory()->create();
        $service = Service::factory()->create(['game_id' => $game->id]);
        $order = Order::factory()->create(['service_id' => $service->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/orders/{$order->id}");

        $response->assertStatus(403);
    }

    public function test_user_can_cancel_pending_order()
    {
        $game = Game::factory()->create();
        $service = Service::factory()->create(['game_id' => $game->id]);
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'service_id' => $service->id,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/orders/{$order->id}/cancel");

        $response->assertStatus(200)
                ->assertJsonPath('success', true);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled'
        ]);
    }

    public function test_user_cannot_cancel_completed_order()
    {
        $game = Game::factory()->create();
        $service = Service::factory()->create(['game_id' => $game->id]);
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'service_id' => $service->id,
            'status' => 'completed'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/orders/{$order->id}/cancel");

        $response->assertStatus(422);
    }

    public function test_guest_cannot_access_orders()
    {
        $response = $this->getJson('/api/orders');

        $response->assertStatus(401);
    }
}
