<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Auth\LoginController;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * M7: the courier's deliveries, from the pick-up to the customer's door.
 */
class CourierDeliveriesTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private User $courier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->courier = User::query()->where('email', LoginController::demoEmail(UserRole::Courier))->firstOrFail();
        $this->actingAs($this->courier);
    }

    public function test_the_list_shows_the_couriers_open_deliveries_and_nobody_elses(): void
    {
        $own = $this->courier->deliveries()->whereIn('status', [OrderStatus::Preparing, OrderStatus::OnTheWay])->get();
        $foreign = Order::query()->where('courier_id', '!=', $this->courier->id)->where('status', OrderStatus::Preparing)->firstOrFail();

        $this->assertTrue($own->isNotEmpty());

        $response = $this->get(route('courier.deliveries.index'))->assertOk();

        foreach ($own as $order) {
            $response->assertSee($order->code);
        }

        $response->assertDontSee($foreign->code);

        $this->getJson(route('courier.deliveries.pending'))
            ->assertJson(['count' => $own->count()])
            ->assertJsonStructure(['count', 'stamp']);
    }

    public function test_a_delivery_goes_from_the_pick_up_to_the_customers_door(): void
    {
        $order = $this->courier->deliveries()->where('status', OrderStatus::Preparing)->firstOrFail();
        $customerNotices = $order->customer->notifications()->count();
        $restaurantNotices = $order->restaurant->owner->notifications()->count();
        $earned = (int) $this->get(route('courier.deliveries.index'))->viewData('earnedToday');

        $this->get(route('courier.deliveries.show', $order))->assertOk()->assertSee('Retiré el pedido');

        // It cannot be delivered before it was picked up.
        $this->post(route('courier.deliveries.deliver', $order))->assertStatus(409);

        $this->post(route('courier.deliveries.pick-up', $order))->assertRedirect();
        $this->assertSame(OrderStatus::OnTheWay, $order->fresh()->status);
        $this->get(route('courier.deliveries.show', $order))->assertSee('Entregué el pedido');

        $this->post(route('courier.deliveries.deliver', $order))->assertRedirect();
        $this->assertSame(OrderStatus::Delivered, $order->fresh()->status);
        $this->get(route('courier.deliveries.show', $order))->assertSee('Entrega completada');

        // The customer hears about both steps, the restaurant about the delivery.
        $this->assertSame($customerNotices + 2, $order->customer->notifications()->count());
        $this->assertSame($restaurantNotices + 1, $order->restaurant->owner->notifications()->count());
        $this->assertSame('Pedido entregado', $order->restaurant->owner->notifications()->latest()->first()->data['title']);

        // The fee of the delivery is the courier's.
        $this->assertSame($earned + $order->delivery_fee, (int) $this->get(route('courier.deliveries.index'))->viewData('earnedToday'));

        $this->post(route('courier.deliveries.deliver', $order))->assertStatus(409);
    }

    public function test_a_courier_cannot_open_or_move_someone_elses_delivery(): void
    {
        $foreign = Order::query()->where('courier_id', '!=', $this->courier->id)->where('status', OrderStatus::Preparing)->firstOrFail();

        $this->get(route('courier.deliveries.show', $foreign))->assertNotFound();
        $this->post(route('courier.deliveries.pick-up', $foreign))->assertNotFound();
        $this->assertSame(OrderStatus::Preparing, $foreign->fresh()->status);
    }

    public function test_without_deliveries_the_list_says_so(): void
    {
        $this->courier->deliveries()->update(['courier_id' => null]);

        $this->get(route('courier.deliveries.index'))->assertOk()->assertSee('Por ahora, a descansar');
    }
}
