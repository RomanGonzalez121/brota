<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Http\Controllers\Auth\LoginController;
use App\Models\Category;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $demoOwner = User::query()->where('email', LoginController::demoEmail(UserRole::Restaurant))->first();
        $demoCustomer = User::query()->where('email', LoginController::demoEmail(UserRole::Customer))->first();

        $demoCustomer->addresses()->createMany([
            ['label' => 'Casa', 'street' => 'Av. Córdoba 5432, 3° B', 'neighborhood' => 'Palermo', 'notes' => 'Timbre 3B. Si no contesto, llamame.', 'is_default' => true],
            ['label' => 'Trabajo', 'street' => 'Humboldt 1550, piso 2', 'neighborhood' => 'Palermo', 'notes' => 'Dejar en recepción.'],
        ]);

        foreach ($this->restaurants() as $index => $data) {
            $category = Category::query()->firstOrCreate(
                ['slug' => Str::slug($data['category'])],
                ['name' => $data['category']],
            );

            // The first restaurant belongs to the demo "restaurante" user.
            $owner = $index === 0 ? $demoOwner : User::factory()->restaurantOwner()->create([
                'name' => $data['owner'],
                'email' => Str::slug($data['name']).'@brota.test',
            ]);

            $restaurant = Restaurant::create([
                'user_id' => $owner->id,
                'category_id' => $category->id,
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'tagline' => $data['tagline'],
                'description' => $data['description'],
                'address' => $data['address'],
                'neighborhood' => $data['neighborhood'],
                'distance_km' => $data['distance_km'],
                'rating' => $data['rating'],
                'delivery_fee' => $data['delivery_fee'],
                'eta_minutes' => $data['eta_minutes'],
                'image' => $this->photo('restaurants', $data['name']),
                'is_featured' => $data['featured'] ?? false,
            ]);

            foreach ($data['dishes'] as $dish) {
                $restaurant->dishes()->create([
                    'section' => $dish[0],
                    'name' => $dish[1],
                    'description' => $dish[2],
                    'price' => $dish[3],
                    'tags' => $dish[4],
                    'image' => $this->photo('dishes', $dish[1]),
                    'is_available' => $dish[5] ?? true,
                ]);
            }
        }
    }

    /**
     * Photos live in public/images, named after the slug of what they show.
     * Their authors and licences are listed in public/images/credits.json.
     */
    private function photo(string $folder, string $name): ?string
    {
        $path = "images/{$folder}/".Str::slug($name).'.jpg';

        return file_exists(public_path($path)) ? $path : null;
    }

    /**
     * Each dish is [section, name, description, price, tags, available?].
     *
     * @return list<array<string, mixed>>
     */
    private function restaurants(): array
    {
        return [
            [
                'name' => 'La Huerta de Palermo', 'owner' => 'Sofía Giménez', 'category' => 'Cocina de huerta', 'featured' => true,
                'tagline' => 'Verduras de estación cosechadas a 40 km',
                'description' => 'Cocinamos con lo que llega cada mañana de quintas agroecológicas de Florencio Varela. La carta cambia con la estación.',
                'address' => 'Gorriti 4850', 'neighborhood' => 'Palermo', 'distance_km' => 1.2, 'rating' => 4.8, 'delivery_fee' => 1500, 'eta_minutes' => 30,
                'dishes' => [
                    ['Entradas', 'Hummus de remolacha', 'Con crudités de la huerta y pan de masa madre.', 6800, ['Vegano']],
                    ['Entradas', 'Empanadas de acelga y queso de cabra', 'Dos unidades al horno de barro.', 5900, ['Vegetariano']],
                    ['Entradas', 'Sopa de calabaza asada', 'Con jengibre y semillas tostadas.', 5400, ['Vegano', 'Sin TACC']],
                    ['Principales', 'Bowl de la huerta', 'Arroz yamaní, vegetales asados, palta, huevo mollet y aderezo de tahini.', 12900, ['Vegetariano', 'Sin TACC']],
                    ['Principales', 'Berenjenas a la parmesana', 'Gratinadas con salsa de tomate y albahaca, con ensalada de hojas verdes.', 13500, ['Vegetariano', 'Sin TACC']],
                    ['Principales', 'Risotto de hongos de pino', 'Arroz carnaroli, hongos de Córdoba y queso sardo.', 14800, ['Vegetariano', 'Sin TACC']],
                    ['Principales', 'Tarta de puerros y zapallitos', 'Masa integral, porción generosa con ensalada.', 10900, ['Vegetariano']],
                    ['Principales', 'Ñoquis de calabaza', 'Con manteca de salvia y nueces.', 13200, ['Vegetariano'], false],
                    ['Postres', 'Flan de huevos de campo', 'Con dulce de leche orgánico.', 5600, ['Sin TACC']],
                    ['Postres', 'Crumble de manzana', 'Avena, canela y helado de crema.', 5900, ['Vegetariano']],
                    ['Bebidas', 'Limonada de menta y jengibre', '500 ml, endulzada con miel.', 3600, ['Sin TACC']],
                    ['Bebidas', 'Kombucha de la casa', '330 ml, sabor frutos rojos.', 4200, ['Vegano', 'Sin TACC']],
                ],
            ],
            [
                'name' => 'Raíz Cocina Viva', 'owner' => 'Bruno Castelli', 'category' => 'Vegano', 'featured' => true,
                'tagline' => 'Cocina a base de plantas, sin ultraprocesados',
                'description' => 'Todo lo que servimos sale de plantas y se hace acá: los quesos de cajú, los panes y los fermentos.',
                'address' => 'Thames 1820', 'neighborhood' => 'Palermo', 'distance_km' => 2.4, 'rating' => 4.7, 'delivery_fee' => 1800, 'eta_minutes' => 35,
                'dishes' => [
                    ['Entradas', 'Bastones de polenta', 'Crocantes, con alioli de ajo asado.', 5800, ['Vegano', 'Sin TACC']],
                    ['Entradas', 'Hongos en escabeche', 'Shiitakes marinados en vinagre de manzana, ajo y laurel.', 7200, ['Vegano', 'Sin TACC']],
                    ['Principales', 'Burger de lentejas y remolacha', 'Pan de papa, cheddar de cajú, pickles y papas rústicas.', 12400, ['Vegano']],
                    ['Principales', 'Curry de garbanzos', 'Leche de coco, espinaca y arroz basmati.', 11900, ['Vegano', 'Sin TACC']],
                    ['Principales', 'Tacos de coliflor', 'Tres tortillas de maíz, coliflor al pastor y guacamole.', 11500, ['Vegano', 'Sin TACC']],
                    ['Principales', 'Lasaña de vegetales', 'Bechamel de castañas y salsa de tomates asados.', 13800, ['Vegano']],
                    ['Postres', 'Cheesecake de cajú', 'Sin lácteos, con base de dátiles y almendras.', 6200, ['Vegano', 'Sin TACC']],
                    ['Bebidas', 'Licuado verde', 'Espinaca, manzana, apio y jengibre. 400 ml.', 4300, ['Vegano', 'Sin TACC']],
                ],
            ],
            [
                'name' => 'Verde que te Quiero', 'owner' => 'Paula Domínguez', 'category' => 'Bowls y ensaladas', 'featured' => true,
                'tagline' => 'Bowls armados en el momento',
                'description' => 'Ensaladas y bowls con hojas hidropónicas del barrio y legumbres de productores cooperativos.',
                'address' => 'Aguirre 780', 'neighborhood' => 'Villa Crespo', 'distance_km' => 0.8, 'rating' => 4.6, 'delivery_fee' => 1200, 'eta_minutes' => 20,
                'dishes' => [
                    ['Entradas', 'Falafel al horno', 'Seis unidades con salsa de yogur y menta.', 6100, ['Vegetariano']],
                    ['Principales', 'Ensalada César de garbanzos', 'Lechuga, garbanzos crocantes, croutons y aderezo de la casa.', 9800, ['Vegetariano']],
                    ['Principales', 'Bowl mediterráneo', 'Cuscús, tomates secos, aceitunas, queso feta y hummus.', 11200, ['Vegetariano']],
                    ['Principales', 'Bowl proteico', 'Quinoa, porotos negros, tomate, cebolla morada y cilantro.', 11900, ['Vegano', 'Sin TACC']],
                    ['Principales', 'Wrap de vegetales grillados', 'Tortilla integral, queso crema de la casa y rúcula.', 9600, ['Vegetariano']],
                    ['Postres', 'Ensalada de frutas de estación', 'Con granola casera.', 4600, ['Vegano']],
                    ['Bebidas', 'Agua saborizada de naranja', 'Con rodajas de naranja y mucho hielo. 500 ml, sin azúcar.', 2900, ['Vegano', 'Sin TACC']],
                ],
            ],
            [
                'name' => 'Masa Madre Chacarita', 'owner' => 'Hernán Vallejos', 'category' => 'Panadería y café',
                'tagline' => 'Pan de fermentación lenta y café de especialidad',
                'description' => 'Horneamos todos los días con harinas orgánicas molidas a piedra. El café es de tostadores locales.',
                'address' => 'Jorge Newbery 3650', 'neighborhood' => 'Chacarita', 'distance_km' => 3.6, 'rating' => 4.9, 'delivery_fee' => 1900, 'eta_minutes' => 40,
                'dishes' => [
                    ['Panadería', 'Pan de masa madre de campo', 'Hogaza de 600 g, harina agroecológica.', 5200, ['Vegano']],
                    ['Panadería', 'Medialunas de manteca', 'Media docena, hojaldradas a mano.', 4800, ['Vegetariano']],
                    ['Panadería', 'Chipá de queso', 'Cuarto kilo, recién horneado.', 4500, ['Vegetariano', 'Sin TACC']],
                    ['Principales', 'Tostón de palta y huevo', 'Pan de masa madre, palta, huevo poché y semillas.', 8900, ['Vegetariano']],
                    ['Principales', 'Sándwich de vegetales asados', 'Focaccia, berenjena, morrón, queso brie y pesto.', 9900, ['Vegetariano']],
                    ['Postres', 'Budín de limón y amapola', 'Porción.', 3900, ['Vegetariano']],
                    ['Postres', 'Alfajor de maicena', 'Con dulce de leche orgánico.', 2800, ['Vegetariano']],
                    ['Bebidas', 'Flat white', 'Café de especialidad, 240 ml.', 3800, ['Sin TACC']],
                ],
            ],
            [
                'name' => 'El Brote Feliz', 'owner' => 'Marina Otero', 'category' => 'Vegetariano', 'featured' => true,
                'tagline' => 'Platos caseros y abundantes',
                'description' => 'Un clásico de Colegiales: cocina vegetariana de olla, pastas amasadas a la vista y pizza de masa madre.',
                'address' => 'Av. Federico Lacroze 2940', 'neighborhood' => 'Colegiales', 'distance_km' => 2.9, 'rating' => 4.5, 'delivery_fee' => 1700, 'eta_minutes' => 35,
                'dishes' => [
                    ['Entradas', 'Provoleta con tomates confitados', 'Orégano fresco y pan tostado.', 7400, ['Vegetariano']],
                    ['Entradas', 'Buñuelos de espinaca', 'Seis unidades con dip de limón.', 5700, ['Vegetariano']],
                    ['Principales', 'Canelones de ricota y verdura', 'Salsa mixta y queso gratinado.', 12600, ['Vegetariano']],
                    ['Principales', 'Pizza de masa madre con rúcula', 'Muzzarella, tomates cherry y parmesano. Cuatro porciones.', 13400, ['Vegetariano']],
                    ['Principales', 'Hamburguesa de garbanzos', 'Con queso, cebolla caramelizada y batatas fritas.', 12200, ['Vegetariano']],
                    ['Postres', 'Panqueques con dulce de leche', 'Dos unidades.', 5200, ['Vegetariano']],
                    ['Bebidas', 'Jugo de naranja exprimido', '400 ml.', 3400, ['Vegano', 'Sin TACC']],
                ],
            ],
            [
                'name' => 'Pura Pulpa', 'owner' => 'Diego Arrieta', 'category' => 'Jugos y licuados',
                'tagline' => 'Fruta fresca, sin azúcar agregada',
                'description' => 'Jugos prensados en frío y licuados con fruta de estación. Los vasos son retornables.',
                'address' => 'Av. Corrientes 5320', 'neighborhood' => 'Villa Crespo', 'distance_km' => 1.7, 'rating' => 4.4, 'delivery_fee' => 1300, 'eta_minutes' => 20,
                'dishes' => [
                    ['Principales', 'Açaí bowl', 'Açaí, banana, granola, coco y miel.', 9200, ['Vegetariano']],
                    ['Principales', 'Tostado integral de queso y tomate', 'En pan de semillas.', 6900, ['Vegetariano']],
                    ['Postres', 'Barrita de cereales casera', 'Avena, miel, almendras y pasas.', 2600, ['Vegetariano']],
                    ['Bebidas', 'Licuado de banana y avena', 'Con leche de almendras. 500 ml.', 4400, ['Vegano']],
                    ['Bebidas', 'Jugo de zanahoria y naranja', 'Con cúrcuma y jengibre. 400 ml.', 4100, ['Vegano', 'Sin TACC']],
                    ['Bebidas', 'Smoothie de frutos rojos', 'Arándanos, frutillas y yogur natural. 500 ml.', 4800, ['Vegetariano', 'Sin TACC']],
                ],
            ],
            [
                'name' => 'Tierra Adentro', 'owner' => 'Ramona Tolaba', 'category' => 'Cocina de huerta',
                'tagline' => 'Recetas del norte con productos agroecológicos',
                'description' => 'Cocina regional sin carne: humitas, tamales y guisos con maíz y zapallo de productores de Jujuy y Salta.',
                'address' => 'Av. Rivadavia 5180', 'neighborhood' => 'Caballito', 'distance_km' => 5.2, 'rating' => 4.6, 'delivery_fee' => 2400, 'eta_minutes' => 50,
                'dishes' => [
                    ['Entradas', 'Humita en chala', 'Dos unidades, receta salteña.', 6900, ['Vegetariano', 'Sin TACC']],
                    ['Entradas', 'Empanadas de verdura', 'Tres unidades, fritas o al horno.', 6300, ['Vegetariano']],
                    ['Principales', 'Locro vegetariano', 'Zapallo, maíz, porotos y salsa picante aparte.', 12800, ['Vegano', 'Sin TACC']],
                    ['Principales', 'Guiso de lentejas', 'Con vegetales de estación y pan casero.', 11400, ['Vegano']],
                    ['Principales', 'Tamales de choclo y queso', 'Dos unidades con ensalada criolla.', 11800, ['Vegetariano', 'Sin TACC']],
                    ['Postres', 'Dulce de cayote con nueces', 'Receta salteña, con queso de cabra aparte.', 5400, ['Vegetariano', 'Sin TACC']],
                    ['Bebidas', 'Mate cocido', 'En saquito, 300 ml.', 2800, ['Vegano', 'Sin TACC']],
                ],
            ],
            [
                'name' => 'Semilla Bodegón Vegano', 'owner' => 'Gastón Lezcano', 'category' => 'Vegano',
                'tagline' => 'El bodegón de siempre, todo a base de plantas',
                'description' => 'Porciones de bodegón porteño en versión vegana: milanesas, pastas y tortillas para compartir.',
                'address' => 'Av. San Juan 480', 'neighborhood' => 'San Telmo', 'distance_km' => 4.1, 'rating' => 4.3, 'delivery_fee' => 2200, 'eta_minutes' => 45,
                'dishes' => [
                    ['Entradas', 'Gírgolas salteadas', 'Con ajo, perejil y limón.', 7600, ['Vegano', 'Sin TACC']],
                    ['Principales', 'Milanesa napolitana de seitán', 'Con papas fritas.', 14200, ['Vegano']],
                    ['Principales', 'Sorrentinos de calabaza', 'Con salsa rosa de cajú.', 13600, ['Vegano']],
                    ['Principales', 'Tortilla de papas sin huevo', 'Babé, hecha con harina de garbanzos.', 10800, ['Vegano', 'Sin TACC']],
                    ['Postres', 'Flan de coco con caramelo', 'Porción individual.', 5300, ['Vegano', 'Sin TACC']],
                    ['Bebidas', 'Soda de sifón', 'Sifón de 500 ml, con rodaja de limón.', 2400, ['Vegano', 'Sin TACC']],
                ],
            ],
        ];
    }
}
