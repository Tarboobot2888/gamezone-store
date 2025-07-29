<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_have_orders()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->orders->contains($order));
    }

    public function test_user_can_have_cart_items()
    {
        $user = User::factory()->create();
        $cartItem = Cart::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->cartItems->contains($cartItem));
    }

    public function test_user_avatar_url_accessor()
    {
        $user = User::factory()->create(['avatar' => null]);
        
        $this->assertStringContainsString('placeholder.svg', $user->avatar_url);
    }

    public function test_user_full_name_accessor()
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        
        $this->assertEquals('John Doe', $user->full_name);
    }

    public function test_user_can_check_role()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->assertTrue($user->hasRole('admin'));
    }
}
