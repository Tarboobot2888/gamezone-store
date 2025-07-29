<?php

namespace Tests\Unit\Models;

use App\Models\Game;
use App\Models\Service;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_belongs_to_game()
    {
        $game = Game::factory()->create();
        $service = Service::factory()->create(['game_id' => $game->id]);

        $this->assertEquals($game->id, $service->game->id);
    }

    public function test_service_has_orders_relationship()
    {
        $service = Service::factory()->create();
        $order = Order::factory()->create(['service_id' => $service->id]);

        $this->assertTrue($service->orders->contains($order));
    }

    public function test_service_formatted_price_attribute()
    {
        $service = Service::factory()->create([
            'price' => 9.99,
            'currency' => 'USD'
        ]);

        $this->assertEquals('9.99 USD', $service->formatted_price);
    }

    public function test_service_scope_active_returns_only_active_services()
    {
        Service::factory()->create(['is_active' => true]);
        Service::factory()->create(['is_active' => false]);

        $activeServices = Service::active()->get();

        $this->assertCount(1, $activeServices);
        $this->assertTrue($activeServices->first()->is_active);
    }

    public function test_service_scope_featured_returns_only_featured_services()
    {
        Service::factory()->create(['is_featured' => true]);
        Service::factory()->create(['is_featured' => false]);

        $featuredServices = Service::featured()->get();

        $this->assertCount(1, $featuredServices);
        $this->assertTrue($featuredServices->first()->is_featured);
    }
}
