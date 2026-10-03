<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\ForumCategory;
use App\Models\ForumReply;
use App\Models\ForumTopic;
use App\Models\User;
use App\Notifications\ActivityNotification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ForumSeeder extends Seeder
{
    public function run(): void
    {
        $authors = User::query()->where('role', UserRole::Customer)->orderBy('id')->get();
        $admin = User::query()->where('role', UserRole::Admin)->first();

        $categories = collect(['Compost en casa', 'Envases y retornables', 'Separación de residuos', 'Huerta urbana', 'Trueque y reutilización'])
            ->mapWithKeys(fn (string $name) => [$name => ForumCategory::create(['name' => $name, 'slug' => Str::slug($name)])]);

        $topics = [];

        foreach ($this->topics() as $index => $data) {
            $createdAt = $data['days_ago'] === 0
                ? now()->subHours(2)
                : now()->subDays($data['days_ago'])->setTime(10 + $index, 15);

            $topic = ForumTopic::create([
                'user_id' => $authors[$data['author']]->id,
                'forum_category_id' => $categories[$data['category']]->id,
                'title' => $data['title'],
                'body' => $data['body'],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            foreach ($data['replies'] as $offset => [$author, $body]) {
                $repliedAt = $createdAt->copy()->addHours(($offset + 1) * 3);

                $topic->replies()->create([
                    'user_id' => $authors[$author]->id,
                    'body' => $body,
                    'created_at' => $repliedAt,
                    'updated_at' => $repliedAt,
                ]);
            }

            $topics[$data['title']] = $topic;
        }

        // Pending reports for the admin's moderation queue.
        $spam = $topics['GANÁ PLATA DESDE TU CASA con suplementos naturales'];
        $spam->reports()->create(['user_id' => $authors[0]->id, 'reason' => 'spam', 'details' => 'No tiene nada que ver con el foro.']);
        $spam->reports()->create(['user_id' => $authors[2]->id, 'reason' => 'spam']);

        $rude = ForumReply::query()->where('body', 'like', 'Si no sabés eso%')->first();
        $rude->reports()->create(['user_id' => $authors[4]->id, 'reason' => 'offensive', 'details' => 'Trata mal a alguien que solo hizo una pregunta.']);

        // One report already handled, so the queue shows history too.
        $handled = $topics['Huerta en balcón con poco sol']->replies()->first();
        $handled->reports()->create([
            'user_id' => $authors[1]->id,
            'reason' => 'off_topic',
            'status' => 'dismissed',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now()->subDays(6),
        ]);

        $admin->notify(new ActivityNotification(
            title: 'Hay denuncias para revisar',
            body: 'Tres denuncias nuevas esperan moderación en el foro.',
            url: route('admin.reports.index', absolute: false),
            icon: 'flag',
        ));
    }

    /**
     * Authors are indexes into the customer list; 0 is the demo customer.
     *
     * @return list<array<string, mixed>>
     */
    private function topics(): array
    {
        return [
            [
                'author' => 1, 'category' => 'Compost en casa', 'days_ago' => 1,
                'title' => 'Mi compostera tiene olor, ¿qué estoy haciendo mal?',
                'body' => "Arranqué hace un mes con una compostera de balcón y desde la semana pasada larga un olor fuerte, como a podrido. Le pongo cáscaras, yerba y restos de verdura.\n\n¿Le falta algo? No quiero que se quejen los vecinos.",
                'replies' => [
                    [3, 'Le falta material seco. Por cada balde de restos húmedos sumale uno de hojas secas, cartón en tiras o aserrín. A mí se me fue el olor en tres días.'],
                    [0, 'Y revolvé una vez por semana para que entre aire. La yerba es buenísima pero compacta mucho.'],
                    [1, '¡Gracias! Le puse cartón de las cajas de huevos y ya mejoró.'],
                ],
            ],
            [
                'author' => 0, 'category' => 'Envases y retornables', 'days_ago' => 2,
                'title' => '¿Cómo devuelven los envases retornables de los pedidos?',
                'body' => "Vengo pidiendo con envases retornables y ya junté seis tuppers de vidrio. ¿Se los dan al repartidor en el próximo pedido o hay que llevarlos al local?",
                'replies' => [
                    [2, 'Yo se los doy al repartidor cuando trae el pedido siguiente. Los lavo antes, eso sí.'],
                    [5, 'En La Huerta de Palermo también los reciben en el local y te suman las hojitas igual.'],
                ],
            ],
            [
                'author' => 4, 'category' => 'Separación de residuos', 'days_ago' => 3,
                'title' => '¿El tetra brik va con los reciclables?',
                'body' => 'Siempre dudo con los cartones de leche y de jugo. Tienen cartón pero también una capa plateada adentro. ¿Van a la bolsa verde o a la negra?',
                'replies' => [
                    [3, 'Van a la bolsa verde. Enjuagalo, aplastalo y listo: las cooperativas lo separan.'],
                    [6, 'Si no sabés eso mejor ni opines, es lo más básico que hay. Qué ignorancia.'],
                    [2, 'En el Punto Verde me dijeron lo mismo que arriba: enjuagado y aplastado.'],
                ],
            ],
            [
                'author' => 2, 'category' => 'Huerta urbana', 'days_ago' => 8,
                'title' => 'Huerta en balcón con poco sol',
                'body' => 'Mi balcón da al sur y le pegan tres horas de sol, con suerte. ¿Qué puedo plantar que no se me muera en dos semanas?',
                'replies' => [
                    [5, 'Hojas verdes: rúcula, lechuga, acelga y perejil andan bien con poco sol. Tomates olvidate.'],
                    [0, 'La menta crece en cualquier lado. Ponela en maceta aparte porque invade todo.'],
                ],
            ],
            [
                'author' => 3, 'category' => 'Trueque y reutilización', 'days_ago' => 5,
                'title' => 'Regalo frascos de vidrio con tapa (Palermo)',
                'body' => 'Tengo unos veinte frascos de mermelada y de aceitunas, limpios y con tapa. Sirven para conservas o para comprar suelto. Los retiran por Palermo, cerca de Plaza Armenia.',
                'replies' => [
                    [1, '¡Me sirven! Te escribo para coordinar.'],
                ],
            ],
            [
                'author' => 5, 'category' => 'Separación de residuos', 'days_ago' => 11,
                'title' => 'Puntos Verdes de Villa Crespo: horarios actualizados',
                'body' => "Pasé por el de Parque Centenario y cambiaron el horario: ahora atienden de miércoles a domingo de 11 a 19.\n\nReciben aceite usado, pilas, electrónicos chicos y reciclables secos.",
                'replies' => [
                    [4, 'Buen dato, fui un martes y estaba cerrado.'],
                ],
            ],
            [
                'author' => 6, 'category' => 'Compost en casa', 'days_ago' => 14,
                'title' => 'Compost con lombrices: por dónde empezar',
                'body' => 'Quiero armar una vermicompostera en el lavadero. ¿Dónde consiguen las lombrices californianas y cuántas hacen falta para dos personas?',
                'replies' => [
                    [3, 'En las ferias de productores suelen vender el núcleo. Con medio kilo arrancás tranquila.'],
                    [2, 'No les des cítricos ni cebolla al principio, se estresan.'],
                ],
            ],
            [
                'author' => 6, 'category' => 'Trueque y reutilización', 'days_ago' => 0,
                'title' => 'GANÁ PLATA DESDE TU CASA con suplementos naturales',
                'body' => 'Sumate a mi equipo de ventas!!! Productos 100% naturales, ganancias desde la primera semana. Escribime por privado y te paso el link de registro.',
                'replies' => [],
            ],
        ];
    }
}
