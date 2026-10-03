<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Auth\LoginController;
use App\Models\Restaurant;
use App\Models\Setting;
use App\Models\User;
use App\Services\Loyalty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * M9: levels of Hojitas Verdes, their monthly benefits and the rules the
 * administrator sets.
 */
class LoyaltyLevelsTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::query()->where('email', LoginController::demoEmail(UserRole::Customer))->firstOrFail();
    }

    public function test_the_level_follows_the_hojitas_collected(): void
    {
        $loyalty = app(Loyalty::class);

        $this->assertSame('Semilla', $loyalty->levelFor(0)['name']);
        $this->assertSame('Semilla', $loyalty->levelFor(39)['name']);
        $this->assertSame('Brote', $loyalty->levelFor(40)['name']);
        $this->assertSame('Planta', $loyalty->levelFor(120)['name']);
        $this->assertSame('Árbol', $loyalty->levelFor(5000)['name']);
        $this->assertSame('Planta', $loyalty->nextLevelFor(40)['name']);
        $this->assertNull($loyalty->nextLevelFor(300));
    }

    public function test_the_customer_sees_their_level_and_what_is_left_this_month(): void
    {
        $this->customer->update(['leaf_balance' => 60]);

        $this->actingAs($this->customer)->get(route('leaves.index'))
            ->assertOk()
            ->assertSee('Nivel Brote')
            ->assertSee('para llegar a Planta')
            ->assertSee('1 envío gratis');
    }

    public function test_a_free_delivery_is_taken_off_and_used_up_for_the_month(): void
    {
        $this->customer->update(['leaf_balance' => 60]);
        $order = $this->placeOrder(Loyalty::FREE_DELIVERY);

        $this->assertSame(Loyalty::FREE_DELIVERY, $order->benefit);
        $this->assertSame($order->delivery_fee, $order->discount);
        $this->assertSame($order->subtotal, $order->total);
        // Restaurant and courier are still paid on the full value.
        $this->assertSame($order->subtotal + $order->delivery_fee, $order->gross());

        $this->assertSame(0, app(Loyalty::class)->remaining($this->customer)[Loyalty::FREE_DELIVERY]);

        // The second order of the month asks for it again and does not get it.
        $second = $this->placeOrder(Loyalty::FREE_DELIVERY);
        $this->assertNull($second->benefit);
        $this->assertSame(0, $second->discount);

        // A rejected order gives the benefit back.
        $order->update(['status' => OrderStatus::Rejected]);
        $this->assertSame(1, app(Loyalty::class)->remaining($this->customer)[Loyalty::FREE_DELIVERY]);
    }

    public function test_a_level_without_the_benefit_cannot_use_it(): void
    {
        $this->customer->update(['leaf_balance' => 10]);

        $order = $this->placeOrder(Loyalty::FREE_ORDER);

        $this->assertNull($order->benefit);
        $this->assertSame($order->gross(), $order->total);
    }

    public function test_the_free_order_of_the_top_level_needs_no_payment_details(): void
    {
        $this->customer->update(['leaf_balance' => 400]);
        $this->fillCart();

        $this->actingAs($this->customer)->post(route('checkout.store'), [
            'address_id' => $this->customer->addresses()->value('id'),
            'payment_method' => 'card',
            'benefit' => Loyalty::FREE_ORDER,
        ])->assertRedirect(route('payment.create'));

        $this->get(route('payment.create'))->assertOk()->assertSee('va por cuenta de Brotá');

        // No card fields are sent at all.
        $this->post(route('payment.store'))->assertRedirect();

        $order = $this->customer->orders()->latest('id')->firstOrFail();
        $this->assertSame(Loyalty::FREE_ORDER, $order->benefit);
        $this->assertSame(0, $order->total);
        $this->assertSame($order->gross(), $order->discount);
        $this->get(route('orders.receipt', $order))->assertOk()->assertSee('Pedido gratis por tu nivel');
    }

    public function test_the_free_order_stops_at_its_cap(): void
    {
        Setting::query()->updateOrCreate(['key' => Setting::FREE_ORDER_CAP], ['value' => '1000', 'label' => 'Tope']);

        $loyalty = app(Loyalty::class);

        $this->assertSame(1000 + 800, $loyalty->discount(Loyalty::FREE_ORDER, 5000, 800));
        $this->assertSame(600 + 800, $loyalty->discount(Loyalty::FREE_ORDER, 600, 800));
    }

    public function test_the_administrator_changes_the_rules(): void
    {
        $admin = User::query()->where('email', LoginController::demoEmail(UserRole::Admin))->firstOrFail();
        $rules = collect(Setting::DEFINITIONS)->map(fn (array $definition) => $definition['default'])->all();

        $this->actingAs($admin)->get(route('admin.settings.edit'))->assertOk()->assertSee('Niveles');

        $this->put(route('admin.settings.update'), [...$rules, Setting::LEVEL_SPROUT_MIN => 20, Setting::SPROUT_FREE_DELIVERIES => 2])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $loyalty = app(Loyalty::class);
        $this->assertSame('Brote', $loyalty->levelFor(25)['name']);
        $this->assertSame(2, $loyalty->levelFor(25)['free_deliveries']);

        // A level cannot start below the one before it.
        $this->put(route('admin.settings.update'), [...$rules, Setting::LEVEL_PLANT_MIN => 30, Setting::LEVEL_SPROUT_MIN => 50])
            ->assertSessionHasErrors(Setting::LEVEL_PLANT_MIN);
    }

    public function test_only_the_administrator_can_change_the_rules(): void
    {
        $this->actingAs($this->customer)->put(route('admin.settings.update'), [])->assertRedirect(route('home'));
        $this->assertSame(40.0, Setting::number(Setting::LEVEL_SPROUT_MIN));
    }

    private function fillCart(): void
    {
        $dish = Restaurant::query()->firstOrFail()->dishes()->where('is_available', true)->firstOrFail();

        $this->withSession(['cart' => ['restaurant_id' => $dish->restaurant_id, 'items' => [$dish->id => 1]]]);
    }

    private function placeOrder(?string $benefit): \App\Models\Order
    {
        $this->fillCart();

        $this->actingAs($this->customer)->post(route('checkout.store'), [
            'address_id' => $this->customer->addresses()->value('id'),
            'payment_method' => 'wallet',
            'benefit' => $benefit,
        ])->assertRedirect(route('payment.create'));

        $this->post(route('payment.store'), ['wallet' => 'MODO'])->assertRedirect();

        return $this->customer->orders()->latest('id')->firstOrFail();
    }
}
