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
 * M5 and the restaurant side of M6: incoming orders, state changes, menu.
 */
class RestaurantPanelTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::query()->where('email', LoginController::demoEmail(UserRole::Restaurant))->firstOrFail();
        $this->actingAs($this->owner);
    }

    public function test_the_board_shows_the_waiting_orders_and_counts_them(): void
    {
        $waiting = $this->owner->restaurant->orders()->where('status', OrderStatus::Received)->get();

        $this->get(route('restaurant.orders.index'))->assertOk()->assertSee($waiting->first()->code);
        $this->getJson(route('restaurant.orders.pending'))
            ->assertJson(['count' => $waiting->count()])
            ->assertJsonStructure(['count', 'stamp']);
    }

    public function test_an_order_can_be_accepted_and_taken_to_delivered(): void
    {
        $order = $this->owner->restaurant->orders()->where('status', OrderStatus::Received)->firstOrFail();
        $notifications = $order->customer->notifications()->count();

        $this->post(route('restaurant.orders.accept', $order))->assertRedirect();
        $this->assertSame(OrderStatus::Preparing, $order->fresh()->status);
        $this->assertNotNull($order->fresh()->courier_id);

        $this->post(route('restaurant.orders.advance', $order))->assertRedirect();
        $this->assertSame(OrderStatus::OnTheWay, $order->fresh()->status);

        // Once it left the kitchen, delivering it is the courier's to report.
        $this->post(route('restaurant.orders.advance', $order))->assertStatus(409);

        $this->actingAs($order->fresh()->courier)->post(route('courier.deliveries.deliver', $order))->assertRedirect();
        $this->assertSame(OrderStatus::Delivered, $order->fresh()->status);

        // The customer hears about each of the three changes.
        $this->assertSame($notifications + 3, $order->customer->notifications()->count());
    }

    public function test_rejecting_needs_a_reason_and_tells_the_customer(): void
    {
        $order = $this->owner->restaurant->orders()->where('status', OrderStatus::Received)->firstOrFail();

        $this->post(route('restaurant.orders.reject', $order), ['reason' => ''])->assertSessionHasErrors('reason');

        $this->post(route('restaurant.orders.reject', $order), ['reason' => 'Nos quedamos sin stock de un plato.'])
            ->assertRedirect(route('restaurant.orders.index'));

        $this->assertSame(OrderStatus::Rejected, $order->fresh()->status);
        $this->assertSame('Tu pedido fue rechazado', $order->customer->notifications()->latest()->first()->data['title']);
    }

    public function test_a_restaurant_cannot_touch_another_restaurants_order(): void
    {
        $foreign = Order::query()->where('restaurant_id', '!=', $this->owner->restaurant->id)->where('status', OrderStatus::Received)->firstOrFail();

        $this->get(route('restaurant.orders.show', $foreign))->assertNotFound();
        $this->post(route('restaurant.orders.accept', $foreign))->assertNotFound();
    }

    public function test_the_menu_can_be_edited(): void
    {
        $this->post(route('restaurant.menu.store'), [
            'section' => 'Postres',
            'name' => 'Peras al malbec',
            'description' => 'Con crema batida y nueces.',
            'price' => 6400,
            'tags' => ['Vegetariano', 'Sin TACC'],
            'is_available' => '1',
        ])->assertRedirect(route('restaurant.menu.index'));

        $dish = $this->owner->restaurant->dishes()->where('name', 'Peras al malbec')->firstOrFail();
        $this->assertSame(['Vegetariano', 'Sin TACC'], $dish->tags);

        $this->put(route('restaurant.menu.update', $dish), [
            'section' => 'Postres',
            'name' => 'Peras al malbec',
            'description' => 'Con crema batida y nueces.',
            'price' => 6900,
        ])->assertRedirect(route('restaurant.menu.index'));
        $this->assertSame(6900, $dish->fresh()->price);
        $this->assertFalse($dish->fresh()->is_available);

        $this->patch(route('restaurant.menu.toggle', $dish))->assertRedirect();
        $this->assertTrue($dish->fresh()->is_available);

        $this->delete(route('restaurant.menu.destroy', $dish))->assertRedirect(route('restaurant.menu.index'));
        $this->assertModelMissing($dish);
    }

    public function test_a_new_order_notifies_the_restaurant(): void
    {
        $this->get(route('notifications.index'))->assertOk()->assertSee('Tenés un pedido nuevo');
    }

    public function test_opening_a_notification_reads_it_and_goes_to_the_order(): void
    {
        $notification = $this->owner->unreadNotifications()->firstOrFail();
        $unread = $this->owner->unreadNotifications()->count();

        $this->get(route('notifications.open', $notification->id))->assertRedirect($notification->data['url']);
        $this->assertSame($unread - 1, $this->owner->unreadNotifications()->count());

        $this->get(route('notifications.index', ['ver' => 'sin-leer']))->assertOk();

        $this->post(route('notifications.read'))->assertRedirect();
        $this->assertSame(0, $this->owner->unreadNotifications()->count());
        $this->get(route('notifications.index', ['ver' => 'sin-leer']))->assertSee('Estás al día');
    }

    public function test_the_report_adds_up_the_delivered_orders_of_the_month(): void
    {
        $month = now()->subMonthNoOverflow()->startOfMonth();
        $sales = $this->owner->restaurant->orders()
            ->where('status', OrderStatus::Delivered)
            ->whereBetween('created_at', [$month, $month->copy()->endOfMonth()])
            ->get();

        $this->assertTrue($sales->isNotEmpty());

        $this->get(route('restaurant.reports.index', ['mes' => $month->format('Y-m')]))
            ->assertOk()
            ->assertViewHas('earnings', (int) $sales->sum('subtotal'))
            ->assertViewHas('topDishes', fn ($dishes) => $dishes->isNotEmpty() && $dishes->first()['units'] >= $dishes->last()['units'])
            ->assertSee('Platos más pedidos');
    }

    public function test_the_report_only_counts_the_restaurants_own_delivered_orders(): void
    {
        $response = $this->get(route('restaurant.reports.index'))->assertOk();
        $before = $response->viewData('earnings');

        // A rejected order of its own and a delivery of another restaurant must not move the number.
        $this->owner->restaurant->orders()->where('status', OrderStatus::Received)->firstOrFail()->update(['status' => OrderStatus::Rejected]);
        Order::query()->where('restaurant_id', '!=', $this->owner->restaurant->id)->where('status', OrderStatus::Received)->firstOrFail()
            ->update(['status' => OrderStatus::Delivered]);

        $this->assertSame($before, $this->get(route('restaurant.reports.index'))->viewData('earnings'));

        // Delivering one of its own does.
        $own = $this->owner->restaurant->orders()->where('status', OrderStatus::Preparing)->firstOrFail();
        $own->update(['status' => OrderStatus::Delivered]);

        $this->assertSame($before + $own->subtotal, $this->get(route('restaurant.reports.index'))->viewData('earnings'));
    }

    public function test_a_month_without_sales_says_so(): void
    {
        $this->owner->restaurant->orders()->delete();

        $this->get(route('restaurant.reports.index'))->assertOk()->assertSee('Todavía no hay ventas');
    }
}
