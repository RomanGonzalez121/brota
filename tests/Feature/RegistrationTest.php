<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * M1: a person can sign up as a customer, a courier or a restaurant.
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_the_sign_up_screen_renders(): void
    {
        $this->get(route('register'))->assertOk()->assertSee('Creá tu cuenta')->assertSee('Tienda');
    }

    public function test_a_customer_can_register(): void
    {
        $this->post(route('register.store'), $this->person('customer'))->assertRedirect(route('home'));

        $user = User::query()->where('email', 'nueva@brota.test')->firstOrFail();

        $this->assertSame(UserRole::Customer, $user->role);
        $this->assertAuthenticatedAs($user);
    }

    public function test_a_courier_registers_with_a_vehicle(): void
    {
        $this->post(route('register.store'), $this->person('courier'))->assertSessionHasErrors('vehicle');

        $this->post(route('register.store'), [...$this->person('courier'), 'vehicle' => 'Bicicleta'])
            ->assertRedirect(route('courier.deliveries.index'));

        $user = User::query()->where('email', 'nueva@brota.test')->firstOrFail();

        $this->assertSame(UserRole::Courier, $user->role);
        $this->assertSame('Bicicleta', $user->vehicle);
    }

    public function test_a_restaurant_registers_with_its_venue_and_appears_once_it_has_a_dish(): void
    {
        $this->post(route('register.store'), $this->person('restaurant'))
            ->assertSessionHasErrors(['restaurant_name', 'category_id', 'address']);

        $this->post(route('register.store'), [
            ...$this->person('restaurant'),
            'restaurant_name' => 'Almacén de Brotes',
            'category_id' => Category::query()->value('id'),
            'tagline' => 'Viandas de estación para llevar',
            'address' => 'Malabia 1200',
            'neighborhood' => 'Villa Crespo',
        ])->assertRedirect(route('restaurant.orders.index'));

        $owner = User::query()->where('email', 'nueva@brota.test')->firstOrFail();
        $restaurant = $owner->restaurant;

        $this->assertSame(UserRole::Restaurant, $owner->role);
        $this->assertSame('almacen-de-brotes', $restaurant->slug);

        // Signed in as the owner: the panel opens and the menu starts empty.
        $this->get(route('restaurant.menu.index'))->assertOk()->assertSee('Tu menú está vacío');

        $this->post(route('restaurant.menu.store'), [
            'section' => 'Principales',
            'name' => 'Vianda de lentejas',
            'description' => 'Con arroz yamaní y vegetales asados.',
            'price' => 9500,
            'tags' => ['Vegano'],
            'is_available' => '1',
        ])->assertRedirect(route('restaurant.menu.index'));

        $customer = User::query()->where('role', UserRole::Customer)->firstOrFail();

        $this->actingAs($customer)->get(route('restaurants.index', ['q' => 'Almacén']))->assertSee('Almacén de Brotes');
    }

    public function test_nobody_can_register_as_administrator(): void
    {
        $this->post(route('register.store'), $this->person('admin'))->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'nueva@brota.test']);
    }

    /**
     * @return array<string, string>
     */
    private function person(string $role): array
    {
        return [
            'role' => $role,
            'name' => 'Nadia Nueva',
            'email' => 'nueva@brota.test',
            'phone' => '11 4444-1234',
            'password' => 'clave-segura-1',
            'password_confirmation' => 'clave-segura-1',
        ];
    }
}
