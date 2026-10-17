<?php

namespace Tests\Feature;

use App\Models\FaqCategory;
use App\Models\FaqItem;
use App\Models\User;
use Database\Seeders\FaqSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqTest extends TestCase
{
    use RefreshDatabase;

    private function headers(): array
    {
        $admin = User::factory()->create(['role' => 'admin']);
        return ['Authorization' => 'Bearer ' . $admin->createToken('test')->plainTextToken];
    }

    public function test_public_gets_seeded_faq_content(): void
    {
        $this->seed(FaqSeeder::class);

        $res = $this->getJson('/api/faq')
            ->assertOk()
            ->assertJsonPath('data.section.title', 'Tout ce que vous')
            ->assertJsonPath('data.section.cta_text', 'Poser une question')
            ->assertJsonCount(4, 'data.categories')
            ->assertJsonCount(5, 'data.items');

        $firstItem = $res->json('data.items.0');
        $this->assertEquals('Comment choisir entre marbre, granit et travertin ?', $firstItem['question']);
        $this->assertEquals('Choix des pierres', $firstItem['category_name']);
        $this->assertTrue($firstItem['is_featured']);
        $this->assertTrue($firstItem['is_active']);
    }

    public function test_public_category_filter_and_search(): void
    {
        $this->seed(FaqSeeder::class);

        // Filter by category slug
        $this->getJson('/api/faq?category=entretien')
            ->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.category_slug', 'entretien');

        // Search query
        $this->getJson('/api/faq?q=Maroc')
            ->assertOk()
            ->assertJsonCount(2, 'data.items');
    }

    public function test_admin_endpoints_require_auth(): void
    {
        $this->getJson('/api/admin/faq')->assertStatus(401);
        $this->postJson('/api/admin/faq/items', ['question' => 'Q?', 'answer' => 'A'])->assertStatus(401);
        $this->postJson('/api/admin/faq/section', ['title' => 'Test'])->assertStatus(401);
    }

    public function test_admin_can_update_section(): void
    {
        $this->seed(FaqSeeder::class);
        $h = $this->headers();

        $this->withHeaders($h)->postJson('/api/admin/faq/section', [
            'eyebrow' => 'FAQ Mise à jour',
            'title' => 'Vos questions',
            'title_em' => 'nos réponses.',
            'description' => 'Un accompagnement pas à pas.',
            'cta_text' => 'Nous contacter',
            'cta_link' => 'https://wa.me/212600112233',
        ])
        ->assertOk()
        ->assertJsonPath('data.eyebrow', 'FAQ Mise à jour')
        ->assertJsonPath('data.title', 'Vos questions')
        ->assertJsonPath('data.cta_link', 'https://wa.me/212600112233');

        // Verify public API reflects the updated section
        $this->getJson('/api/faq')
            ->assertOk()
            ->assertJsonPath('data.section.title', 'Vos questions')
            ->assertJsonPath('data.section.cta_link', 'https://wa.me/212600112233');
    }

    public function test_admin_can_crud_hide_and_reorder_questions(): void
    {
        $this->seed(FaqSeeder::class);
        $h = $this->headers();

        // 1. Create a question
        $created = $this->withHeaders($h)->postJson('/api/admin/faq/items', [
            'question' => 'Quelle est l\'épaisseur standard des tranches ?',
            'answer' => "Nos tranches sont découpées standard en 2 cm et 3 cm d'épaisseur.",
            'category_id' => FaqCategory::where('slug', 'choix-pierres')->value('id'),
            'is_featured' => true,
            'is_active' => true,
        ])
        ->assertStatus(201)
        ->assertJsonPath('data.question', 'Quelle est l\'épaisseur standard des tranches ?')
        ->assertJsonPath('data.category_slug', 'choix-pierres');

        $newId = $created->json('data.id');

        // 2. Update question
        $this->withHeaders($h)->postJson("/api/admin/faq/items/{$newId}", [
            'question' => 'Quelle est l\'épaisseur standard (2cm ou 3cm) ?',
            'answer' => "Standard 2cm et 3cm, sur-mesure sur demande.",
            'is_featured' => false,
            'is_active' => true,
        ])
        ->assertOk()
        ->assertJsonPath('data.question', 'Quelle est l\'épaisseur standard (2cm ou 3cm) ?')
        ->assertJsonPath('data.is_featured', false);

        // 3. Hide question and verify public excludes it
        $this->withHeaders($h)->postJson("/api/admin/faq/items/{$newId}", [
            'question' => 'Quelle est l\'épaisseur standard (2cm ou 3cm) ?',
            'answer' => "Standard 2cm et 3cm, sur-mesure sur demande.",
            'is_active' => false,
        ])->assertOk();

        $this->getJson('/api/faq')->assertJsonCount(5, 'data.items');
        $this->withHeaders($h)->getJson('/api/admin/faq')->assertJsonCount(6, 'data.items');

        // 4. Reorder questions
        $allIds = FaqItem::orderBy('id', 'desc')->pluck('id')->toArray();
        $this->withHeaders($h)->postJson('/api/admin/faq/reorder', ['ids' => $allIds])
            ->assertOk();

        $this->assertEquals($allIds[0], FaqItem::where('position', 1)->value('id'));

        // 5. Delete question
        $this->withHeaders($h)->deleteJson("/api/admin/faq/items/{$newId}")
            ->assertOk();

        $this->assertDatabaseMissing('faq_items', ['id' => $newId]);
    }

    public function test_admin_can_crud_and_reorder_categories(): void
    {
        $this->seed(FaqSeeder::class);
        $h = $this->headers();

        // 1. Create Category
        $catRes = $this->withHeaders($h)->postJson('/api/admin/faq/categories', [
            'name' => 'Tarifs & Chiffrage',
        ])
        ->assertStatus(201)
        ->assertJsonPath('data.name', 'Tarifs & Chiffrage')
        ->assertJsonPath('data.slug', 'tarifs-chiffrage');

        $catId = $catRes->json('data.id');

        // 2. Attach a question to it
        $item = FaqItem::first();
        $item->update(['category_id' => $catId]);

        // 3. Update Category
        $this->withHeaders($h)->postJson("/api/admin/faq/categories/{$catId}", [
            'name' => 'Tarifs & Devis sur mesure',
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Tarifs & Devis sur mesure');

        // 4. Reorder Categories
        $catIds = FaqCategory::orderBy('id', 'desc')->pluck('id')->toArray();
        $this->withHeaders($h)->postJson('/api/admin/faq/categories/reorder', ['ids' => $catIds])
            ->assertOk();

        $this->assertEquals($catIds[0], FaqCategory::where('position', 1)->value('id'));

        // 5. Delete category: verify questions remain with category_id set to null
        $this->withHeaders($h)->deleteJson("/api/admin/faq/categories/{$catId}")
            ->assertOk();

        $this->assertDatabaseMissing('faq_categories', ['id' => $catId]);
        $this->assertDatabaseHas('faq_items', ['id' => $item->id, 'category_id' => null]);
    }
}
