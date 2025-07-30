<?php
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

it('shows registration page', function () {
    $response = $this->get('/register');
    $response->assertStatus(200);
});
