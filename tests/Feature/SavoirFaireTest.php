<?php

namespace Tests\Feature;

use App\Models\SavoirFaireItem;
use App\Models\User;
use Database\Seeders\SavoirFaireSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavoirFaireTest extends TestCase
{
    use RefreshDatabase;

    private function adminToken(): string
    {
        $admin = User::factory()->create(['role' => 'admin']);
        return $admin->createToken('test')->plainTextToken;
    }

    public function test_public_gets_seeded_content_in_order(): void
    {
        $this->seed(SavoirFaireSeeder::class);

        $this->getJson('/api/savoir-faire')
            ->assertOk()
            ->assertJsonPath('data.section.eyebrow', 'Notre expertise')
            ->assertJsonCount(5, 'data.items')
            ->assertJsonPath('data.items.0.title', 'Revêtements muraux & sols');
    }

    public function test_hidden_items_are_not_public(): void
    {
        $this->seed(SavoirFaireSeeder::class);
        SavoirFaireItem::first()->update(['is_active' => false]);

        $this->getJson('/api/savoir-faire')->assertJsonCount(4, 'data.items');
    }

    public function test_admin_endpoints_require_auth(): void
    {
        $this->postJson('/api/admin/savoir-faire/items', ['title' => 'x'])->assertStatus(401);
    }

    public function test_admin_can_crud_and_reorder(): void
    {
        $this->seed(SavoirFaireSeeder::class);
        $h = ['Authorization' => 'Bearer ' . $this->adminToken()];

        $id = $this->withHeaders($h)->postJson('/api/admin/savoir-faire/items', ['title' => 'Nouveau'])
            ->assertStatus(201)->json('data.id');

        $this->withHeaders($h)->postJson("/api/admin/savoir-faire/items/{$id}", ['title' => 'Modifié', 'is_active' => false])
            ->assertOk()->assertJsonPath('data.title', 'Modifié');

        $ids = SavoirFaireItem::orderByDesc('position')->pluck('id')->all();
        $this->withHeaders($h)->postJson('/api/admin/savoir-faire/reorder', ['ids' => $ids])->assertOk();
        $this->assertSame($ids[0], SavoirFaireItem::orderBy('position')->first()->id);

        $this->withHeaders($h)->postJson('/api/admin/savoir-faire/section', ['title' => 'Titre'])
            ->assertOk()->assertJsonPath('data.title', 'Titre');

        $this->withHeaders($h)->deleteJson("/api/admin/savoir-faire/items/{$id}")->assertOk();
        $this->assertDatabaseMissing('savoir_faire_items', ['id' => $id]);
    }
}
