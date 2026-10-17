<?php

namespace Database\Seeders;

use App\Models\MateriauxItem;
use App\Models\RealisationsCategory;
use App\Models\RealisationsItem;
use App\Models\RealisationsSection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RealisationsSeeder extends Seeder
{
    /**
     * Seed the Réalisations tables with initial content.
     */
    public function run(): void
    {
        // 1. Singleton section header
        RealisationsSection::query()->delete();
        RealisationsSection::create([
            'hero_eyebrow' => "Portfolio d'Exception · Maroc",
            'hero_title' => 'Toutes nos',
            'hero_title_em' => 'réalisations.',
            'hero_description' => "Explorez l'ensemble de nos chantiers sur mesure : façades sculptées, escaliers hélicoïdaux, cuisines en granit, salles de bain en marbre et piscines en travertin partout au Maroc.",

            'bento_eyebrow' => 'Réalisations',
            'bento_title' => 'Des projets qui',
            'bento_title_em' => 'portent notre signature.',
            'bento_description' => "Découvrez une sélection de nos chantiers d'exception réalisés sur mesure partout au Maroc.",

            'cta_title' => 'Un projet sur mesure',
            'cta_title_em' => 'en pierre naturelle ?',
            'cta_description' => "Nos artisans tailleurs et maîtres poseurs se déplacent sur l'ensemble du Royaume pour vous conseiller, étudier vos plans et vous délivrer une proposition chiffrée détaillée sous 24h.",
        ]);

        // Clear existing records
        DB::table('realisations_materiaux')->delete();
        DB::table('realisations_images')->delete();
        RealisationsItem::query()->delete();
        RealisationsCategory::query()->delete();

        // 2. Categories
        $categoriesData = [
            ['facade', 'Façades & Extérieurs', 'Façade & Parement', 1],
            ['escalier', 'Escaliers & Sols', "Escalier & Sol d'exception", 2],
            ['cuisine', 'Cuisines & Plans', 'Cuisine haut de gamme', 3],
            ['salle-de-bain', 'Salles de bain & Vasques', 'Salle de bain & Bien-être', 4],
            ['piscine', 'Piscines & Terrasses', 'Piscine & Aménagement extérieur', 5],
        ];

        $categoryMap = [];
        foreach ($categoriesData as [$slug, $name, $badge, $pos]) {
            $cat = RealisationsCategory::create([
                'slug' => $slug,
                'name' => $name,
                'badge_label' => $badge,
                'position' => $pos,
                'is_active' => true,
            ]);
            $categoryMap[$slug] = $cat->id;
        }

        // Cache existing materiaux by slug
        $materials = MateriauxItem::pluck('id', 'slug')->toArray();

        // 3. Projects
        $projects = [
            [
                'id' => 1,
                'slug' => 'villa-contemporaine-taza',
                'category' => 'facade',
                'title' => 'Villa contemporaine en pierre de Taza éclatée',
                'subtitle' => 'Façade · Pierre de Taza',
                'short_title' => 'Villa en pierre de Taza éclatée',
                'short_description' => "Habillage sculptural d'une villa haut standing. Joints invisibles et découpes d'angles biseautées au millimètre.",
                'description' => "Habillage complet des façades d'une villa de maître avec appareillage de pierre de Taza taillée en parement éclaté. Lignes pures contemporaines, isolation naturelle et résistance optimale aux embruns marins.",
                'stone' => 'Pierre de Taza beige',
                'finish' => 'Éclatée brute & Adoucie',
                'location' => 'Rabat (Harhoura)',
                'cover_image' => '/images/realisation-facade-taza.jpg',
                'grid_class' => 't1',
                'is_featured' => true,
                'mat_slug' => 'taza',
            ],
            [
                'id' => 2,
                'slug' => 'escalier-helicoidal-calacatta',
                'category' => 'escalier',
                'title' => 'Escalier hélicoïdal en marbre Calacatta veiné',
                'subtitle' => 'Escalier · Marbre noble',
                'short_title' => 'Marbre veiné, nez de marche poli',
                'short_description' => "Courbes fluides et veines bookmatched. Un travail d'orfèvre exécuté dans une résidence privée de prestige.",
                'description' => "Taille et pose d'un escalier monumental avec marches massives suspendues. Continuité parfaite des veines de marbre d'une marche à l'autre (effet livre ouvert) et nez de marche adouci antidérapant.",
                'stone' => 'Marbre Calacatta noble',
                'finish' => 'Poli brillant miroir',
                'location' => 'Casablanca (Anfa)',
                'cover_image' => '/images/realisation-escalier-marbre.jpg',
                'grid_class' => 't2',
                'is_featured' => true,
                'mat_slug' => 'marbre',
            ],
            [
                'id' => 3,
                'slug' => 'ilot-monolithique-granit-noir',
                'category' => 'cuisine',
                'title' => 'Îlot central monolithique & plan de travail en granit noir',
                'subtitle' => 'Cuisine · Granit absolu',
                'short_title' => 'Plan de travail en granit noir',
                'short_description' => "Finition satinée velours d'une extrême résistance aux rayures, taches et chaleurs intenses.",
                'description' => "Conception et découpe numérique d'un îlot de 4,20 mètres taillé d'un seul tenant en granit noir. Intégration sous plan d'évier affleurant, rainurage d'égouttoir taillé dans la masse et résistance totale aux chocs thermiques.",
                'stone' => 'Granit noir absolu',
                'finish' => 'Satiné adouci & hydrofugé',
                'location' => 'Marrakech (Palmeraie)',
                'cover_image' => '/images/realisation-cuisine-granit.jpg',
                'grid_class' => 't3',
                'is_featured' => true,
                'mat_slug' => 'granit',
            ],
            [
                'id' => 4,
                'slug' => 'suite-vasque-sculptee-ardoise',
                'category' => 'salle-de-bain',
                'title' => 'Suite parentale & vasque sculptée en ardoise naturelle',
                'subtitle' => 'Salle de bain · Ardoise',
                'short_title' => 'Ardoise & vasque monolithique sculptée',
                'short_description' => "Ambiance spa minérale avec contraste entre textures brutes naturelles et raccords d'angles précis.",
                'description' => "Aménagement complet d'une salle de bain minérale comprenant un bloc vasque sculpté à la main dans un bloc monolithique d'ardoise, receveur de douche à l'italienne sans ressaut et parements muraux hydrofugés.",
                'stone' => "Ardoise naturelle d'Atlas",
                'finish' => 'Brute texturée & Brossée',
                'location' => 'Tanger',
                'cover_image' => '/images/realisation-salle-de-bain.jpg',
                'grid_class' => 't4',
                'is_featured' => true,
                'mat_slug' => 'ardoise',
            ],
            [
                'id' => 5,
                'slug' => 'plage-piscine-travertin-romain',
                'category' => 'piscine',
                'title' => 'Plage de piscine & terrasse panoramique en travertin romain',
                'subtitle' => 'Extérieur · Travertin',
                'short_title' => 'Terrasse et plage de piscine antidérapantes',
                'short_description' => 'Teintes chaudes miel et beige naturel. Margelles moulurées sur mesure pour bassin à débordement.',
                'description' => 'Dallage de 350 m² et margelles de piscine galbées à double débordement en travertin naturel. Confort thermique exceptionnel (la pierre reste fraîche même sous fort ensoleillement) et adhérence pieds nus sécurisée.',
                'stone' => 'Travertin romain beige',
                'finish' => 'Brossé vieilli antidérapant R11',
                'location' => 'Marrakech',
                'cover_image' => '/images/realisation-piscine-travertin.jpg',
                'grid_class' => 't5',
                'is_featured' => true,
                'mat_slug' => 'travertin',
            ],
            [
                'id' => 6,
                'slug' => 'facade-pierre-de-taille-bouskoura',
                'category' => 'facade',
                'title' => "Façade d'immeuble résidentiel en pierre de taille",
                'subtitle' => 'Façade · Pierre de taille',
                'short_title' => 'Parement pierre de taille résidentiel',
                'short_description' => 'Moulures et encadrements de baies vitrées taillés dans la masse pour une pérennité centenaire.',
                'description' => "Habillage de colonnes, bandeaux d'étages et corniches architecturales d'une résidence de standing à Bouskoura. Façonnage numérique en atelier et assemblage sur ossature métallique agrée.",
                'stone' => 'Pierre calcaire de Taza',
                'finish' => 'Bouchardée fine',
                'location' => 'Casablanca (Bouskoura)',
                'cover_image' => '/images/realisation-facade-taza.jpg',
                'grid_class' => null,
                'is_featured' => false,
                'mat_slug' => 'taza',
            ],
            [
                'id' => 7,
                'slug' => 'dallage-hall-marbre-noir-blanc',
                'category' => 'escalier',
                'title' => 'Dallage de grand hall & marches en marbre noir et blanc',
                'subtitle' => 'Sol · Marqueterie',
                'short_title' => 'Dallage en marbre noir & blanc',
                'short_description' => 'Calepinage complexe aux lignes épurées. Cristallisation haute brillance pour une durabilité extrême.',
                'description' => "Calepinage géométrique sur mesure pour le grand salon et hall d'accueil d'un riad contemporain. Motifs personnalisés en marqueterie de marbre assemblés et posés sans aucun désaffleurement.",
                'stone' => 'Marbre Noir Marquina & Blanc Volakas',
                'finish' => 'Poli cristallisé',
                'location' => 'Fès',
                'cover_image' => '/images/realisation-escalier-marbre.jpg',
                'grid_class' => null,
                'is_featured' => false,
                'mat_slug' => 'marbre',
            ],
            [
                'id' => 8,
                'slug' => 'cuisine-granit-flamme-rabat',
                'category' => 'cuisine',
                'title' => 'Cuisine contemporaine avec crédence en granit flammé',
                'subtitle' => 'Cuisine · Granit flammé',
                'short_title' => 'Plans de travail en granit flammé',
                'short_description' => 'Harmonie avec les boiseries nobles. Traitement anti-taches oléofuge de qualité professionnelle.',
                'description' => 'Ensemble coordonné de plans de travail et crédence pleine hauteur en granit flammé. Texture minérale soyeuse au toucher, découpes précises pour prises de courant et luminaires intégrés.',
                'stone' => 'Granit gris anthracite flammé',
                'finish' => 'Flammé brossé doux',
                'location' => 'Rabat',
                'cover_image' => '/images/realisation-cuisine-granit.jpg',
                'grid_class' => null,
                'is_featured' => false,
                'mat_slug' => 'granit',
            ],
        ];

        foreach ($projects as $index => $p) {
            $item = RealisationsItem::create([
                'category_id' => $categoryMap[$p['category']] ?? null,
                'slug' => $p['slug'],
                'position' => $index + 1,
                'title' => $p['title'],
                'subtitle' => $p['subtitle'],
                'short_title' => $p['short_title'],
                'short_description' => $p['short_description'],
                'description' => $p['description'],
                'stone' => $p['stone'],
                'finish' => $p['finish'],
                'location' => $p['location'],
                'cover_image' => $p['cover_image'],
                'grid_class' => $p['grid_class'],
                'is_featured' => $p['is_featured'],
                'is_active' => true,
            ]);

            // Link to materiaux if found
            if (!empty($p['mat_slug']) && isset($materials[$p['mat_slug']])) {
                $item->materiaux()->sync([$materials[$p['mat_slug']]]);
            }
        }
    }
}
