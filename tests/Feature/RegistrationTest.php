<?php
 yg374e-codex/refactor-and-productionize-gamezonestore

namespace Tests\Feature;

use Tests\TestCase;

class RegistrationTest extends TestCase
{
    public function test_registration_page_loads(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
    }
}

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

it('shows registration page', function () {
    $response = $this->get('/register');
    $response->assertStatus(200);
});

main