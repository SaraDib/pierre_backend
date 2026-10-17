<?php

namespace Tests\Feature;

use App\Models\MateriauxItem;
use App\Models\User;
use Database\Seeders\MateriauxSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MateriauxTest extends TestCase
{
    use RefreshDatabase;

    private function headers(): array
    {
        $admin = User::factory()->create(['role' => 'admin']);
        return ['Authorization' => 'Bearer ' . $admin->createToken('test')->plainTextToken];
    }

    public function test_public_gets_seeded_content_with_hidden_item_excluded(): void
    {
        $this->seed(MateriauxSeeder::class);

        $this->getJson('/api/materiaux')
            ->assertOk()
            ->assertJsonPath('data.section.title', 'Choisissez votre')
            ->assertJsonCount(4, 'data.items')
            ->assertJsonPath('data.items.0.slug', 'marbre')
            ->assertJsonPath('data.items.0.category_name', 'Intérieur')
            ->assertJsonPath('data.items.0.finishes.0', 'Poli')
            ->assertJsonCount(3, 'data.categories')
            // texture library still contains the hidden material
            ->assertJsonCount(5, 'data.textures');
    }

    public function test_public_category_filter(): void
    {
        $this->seed(MateriauxSeeder::class);

        $this->getJson('/api/materiaux?category=facades')
            ->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.slug', 'taza');
    }

    public function test_admin_endpoints_require_auth(): void
    {
        $this->postJson('/api/admin/materiaux/items', ['name' => 'x'])->assertStatus(401);
        $this->getJson('/api/admin/materiaux')->assertStatus(401);
    }

    public function test_admin_can_crud_hide_and_reorder_materials(): void
    {
        $this->seed(MateriauxSeeder::class);
        $h = $this->headers();

        $created = $this->withHeaders($h)->postJson('/api/admin/materiaux/items', [
            'name' => 'Onyx Vert',
            'description' => 'Translucide',
            'finishes' => ['Poli'],
            'texture_type' => 'veins',
            'base' => '#112233',
        ])->assertStatus(201)->assertJsonPath('data.slug', 'onyx-vert');
        $id = $created->json('data.id');

        $this->getJson('/api/materiaux')->assertJsonCount(5, 'data.items');

        $this->withHeaders($h)->postJson("/api/admin/materiaux/items/{$id}", ['name' => 'Onyx', 'is_active' => false])
            ->assertOk()->assertJsonPath('data.slug', 'onyx-vert');
        $this->getJson('/api/materiaux')->assertJsonCount(4, 'data.items');

        $ids = MateriauxItem::orderByDesc('position')->pluck('id')->all();
        $this->withHeaders($h)->postJson('/api/admin/materiaux/reorder', ['ids' => $ids])->assertOk();
        $this->assertSame($ids[0], MateriauxItem::orderBy('position')->first()->id);

        $this->withHeaders($h)->deleteJson("/api/admin/materiaux/items/{$id}")->assertOk();
        $this->assertDatabaseMissing('materiaux_items', ['id' => $id]);
    }

    public function test_validation_rejects_bad_texture_values(): void
    {
        $h = $this->headers();

        $this->withHeaders($h)->postJson('/api/admin/materiaux/items', ['name' => 'X', 'base' => 'red'])
            ->assertStatus(422)->assertJsonValidationErrors(['base']);
        $this->withHeaders($h)->postJson('/api/admin/materiaux/items', ['name' => 'X', 'texture_type' => 'lava'])
            ->assertStatus(422)->assertJsonValidationErrors(['texture_type']);
    }

    public function test_category_crud_and_deleting_category_keeps_materials(): void
    {
        $this->seed(MateriauxSeeder::class);
        $h = $this->headers();

        $cat = $this->withHeaders($h)->postJson('/api/admin/materiaux/categories', ['name' => 'Sols'])
            ->assertStatus(201)->json('data');
        $this->withHeaders($h)->postJson("/api/admin/materiaux/categories/{$cat['id']}", ['name' => 'Sols & murs'])
            ->assertOk()->assertJsonPath('data.name', 'Sols & murs');

        $marbre = MateriauxItem::where('slug', 'marbre')->first();
        $this->withHeaders($h)->postJson("/api/admin/materiaux/items/{$marbre->id}", ['name' => 'Marbre', 'category_id' => $cat['id']])->assertOk();

        $this->withHeaders($h)->deleteJson("/api/admin/materiaux/categories/{$cat['id']}")->assertOk();
        $this->assertNull($marbre->fresh()->category_id);
        $this->assertDatabaseHas('materiaux_items', ['slug' => 'marbre']);
    }

    public function test_deleting_material_clears_references_in_other_sections(): void
    {
        $this->seed(MateriauxSeeder::class);
        $this->seed(\Database\Seeders\SavoirFaireSeeder::class);
        $h = $this->headers();

        $granit = MateriauxItem::where('slug', 'granit')->first();
        $this->withHeaders($h)->deleteJson("/api/admin/materiaux/items/{$granit->id}")->assertOk();

        $this->assertDatabaseMissing('savoir_faire_items', ['mat_key' => 'granit']);
    }
}
