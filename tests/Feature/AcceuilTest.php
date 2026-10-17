<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AcceuilTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_get_acceuil_content(): void
    {
        $response = $this->getJson('/api/acceuil');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.title_line1', 'La pierre,');
    }

    public function test_admin_can_update_acceuil_content(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin_test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $token = $admin->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/admin/acceuil', [
                'title_line1' => 'La nouvelle pierre,',
                'title_line2' => 'sculptée',
                'title_line3' => 'à vie.',
                'stat1_number' => '45+',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.title_line1', 'La nouvelle pierre,')
            ->assertJsonPath('data.stat1_number', '45+');
    }
}
