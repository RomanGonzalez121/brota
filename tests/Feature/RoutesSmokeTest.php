<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Auth\LoginController;
use App\Models\ForumReport;
use App\Models\ForumTopic;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Opens every GET screen of each role with the seeded demo user and checks it
 * renders. Run it after each stage: `php artisan test`.
 */
class RoutesSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_guests_are_sent_to_the_login_screen(): void
    {
        $this->get('/')->assertRedirect(route('login'));
        $this->get(route('login'))->assertOk()->assertSee('Entrar como cliente');
    }

    #[DataProvider('roles')]
    public function test_every_screen_of_the_role_renders(UserRole $role): void
    {
        $user = User::query()->where('email', LoginController::demoEmail($role))->firstOrFail();
        $parameters = $this->sampleParameters($role, $user);

        $routes = collect(Route::getRoutes()->getRoutes())->filter(fn (RoutingRoute $route) => in_array('GET', $route->methods(), true)
            && in_array('auth', $route->gatherMiddleware(), true)
            && $route->getName() !== 'notifications.open'
            && (in_array("role:{$role->value}", $route->gatherMiddleware(), true) || ! $this->isRoleScoped($route)));

        $this->assertNotEmpty($routes);

        // The checkout screens only open with something in the cart.
        $dish = Restaurant::query()->first()->dishes()->where('is_available', true)->first();
        $this->withSession([
            'cart' => ['restaurant_id' => $dish->restaurant_id, 'items' => [$dish->id => 2]],
            'checkout' => ['address_id' => $user->addresses()->value('id'), 'payment_method' => 'card', 'returnable_containers' => true],
        ]);

        foreach ($routes as $route) {
            $url = route($route->getName(), array_intersect_key($parameters, array_flip($route->parameterNames())));

            $this->actingAs($user)->get($url)->assertOk();
        }
    }

    #[DataProvider('roles')]
    public function test_demo_login_lands_on_the_role_home(UserRole $role): void
    {
        $this->post(route('login.demo', $role->value))->assertRedirect(route($role->homeRoute()));
    }

    public function test_a_role_cannot_open_another_roles_area(): void
    {
        $customer = User::query()->where('email', LoginController::demoEmail(UserRole::Customer))->firstOrFail();

        $this->actingAs($customer)->get(route('admin.restaurants.index'))->assertRedirect(route('home'));
    }

    /**
     * @return array<string, array{UserRole}>
     */
    public static function roles(): array
    {
        return [
            'customer' => [UserRole::Customer],
            'courier' => [UserRole::Courier],
            'restaurant' => [UserRole::Restaurant],
            'admin' => [UserRole::Admin],
        ];
    }

    private function isRoleScoped(RoutingRoute $route): bool
    {
        return collect($route->gatherMiddleware())->contains(fn ($middleware) => is_string($middleware) && str_starts_with($middleware, 'role:'));
    }

    /**
     * A record of each kind that the role is allowed to open.
     *
     * @return array<string, mixed>
     */
    private function sampleParameters(UserRole $role, User $user): array
    {
        $order = match ($role) {
            UserRole::Customer => $user->orders()->where('status', OrderStatus::OnTheWay)->first(),
            UserRole::Courier => $user->deliveries()->where('status', OrderStatus::OnTheWay)->first(),
            UserRole::Restaurant => $user->restaurant->orders()->where('status', OrderStatus::Received)->first(),
            UserRole::Admin => Order::query()->first(),
        };

        return [
            'order' => $order,
            'restaurant' => Restaurant::query()->first(),
            'dish' => ($user->restaurant ?? Restaurant::query()->first())->dishes()->first(),
            'topic' => ForumTopic::query()->visible()->first(),
            'address' => $user->addresses()->first(),
            'courier' => User::query()->where('role', UserRole::Courier)->first(),
            'report' => ForumReport::query()->first(),
        ];
    }
}
