<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Auth\LoginController;
use App\Models\ForumReport;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\OrderWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Walks the parts of the mockup that really work: cart in session, simulated
 * payment, order state changes and Hojitas Verdes.
 */
class CustomerOrderFlowTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_a_customer_can_order_pay_and_earn_leaves_when_it_is_delivered(): void
    {
        $customer = User::query()->where('email', LoginController::demoEmail(UserRole::Customer))->firstOrFail();
        $restaurant = Restaurant::query()->where('slug', 'la-huerta-de-palermo')->firstOrFail();
        $dish = $restaurant->dishes()->where('is_available', true)->firstOrFail();
        $balance = $customer->leaf_balance;

        $this->actingAs($customer);

        $this->post(route('cart.store', $dish))->assertRedirect();
        $this->post(route('cart.store', $dish))->assertRedirect();
        $this->get(route('cart.show'))->assertOk()->assertSee($dish->name);

        $this->post(route('checkout.store'), [
            'address_id' => $customer->addresses()->value('id'),
            'payment_method' => 'card',
            'returnable_containers' => '1',
        ])->assertRedirect(route('payment.create'));

        $response = $this->post(route('payment.store'), [
            'card_number' => '4509 9535 6623 3704',
            'card_name' => 'Lucia Fernandez',
            'card_expiry' => '11/29',
            'card_cvv' => '123',
        ]);

        $order = Order::query()->latest('id')->firstOrFail();

        $response->assertRedirect(route('orders.receipt', $order));
        $this->assertSame(OrderStatus::Received, $order->status);
        $this->assertSame($dish->price * 2 + $restaurant->delivery_fee, $order->total);
        $this->assertSame('3704', $order->payment->card_last4);
        $this->get(route('cart.show'))->assertSee('Tu carrito está vacío');
        $this->assertSame(1, $restaurant->owner->unreadNotifications()->where('data', 'like', "%{$order->id}%")->count());

        $workflow = app(OrderWorkflow::class);
        $workflow->accept($order);
        $workflow->moveTo($order, OrderStatus::OnTheWay);
        $workflow->moveTo($order, OrderStatus::Delivered);

        // La Huerta is nearby (10) and the order used returnable containers (15).
        $this->assertSame(25, $order->fresh()->leaves_earned);
        $this->assertSame($balance + 25, $customer->fresh()->leaf_balance);
        $this->assertNotNull($order->fresh()->courier_id);

        $this->get(route('orders.show', $order))->assertOk()->assertSee('Sumaste 25 Hojitas Verdes');
    }

    public function test_the_menu_adds_dishes_in_the_background(): void
    {
        $customer = User::query()->where('email', LoginController::demoEmail(UserRole::Customer))->firstOrFail();
        $dish = Restaurant::query()->firstOrFail()->dishes()->where('is_available', true)->firstOrFail();

        $this->actingAs($customer);
        $this->postJson(route('cart.store', $dish))->assertOk();

        // No redirect and no reload: the answer carries what the page has to update.
        $this->postJson(route('cart.store', $dish))->assertOk()->assertJson([
            'count' => 2,
            'subtotal' => money($dish->price * 2),
            'dish' => $dish->id,
            'quantity' => 2,
            'replaced' => false,
        ]);
    }

    public function test_adding_a_dish_from_another_restaurant_starts_a_new_cart(): void
    {
        $customer = User::query()->where('email', LoginController::demoEmail(UserRole::Customer))->firstOrFail();
        [$first, $second] = Restaurant::query()->limit(2)->get()->map(fn ($restaurant) => $restaurant->dishes()->where('is_available', true)->first());

        $this->actingAs($customer);
        $this->post(route('cart.store', $first));
        $this->post(route('cart.store', $second));

        $this->get(route('cart.show'))->assertSee($second->name)->assertDontSee($first->name);
    }

    public function test_a_customer_cannot_open_someone_elses_order(): void
    {
        $customer = User::query()->where('email', LoginController::demoEmail(UserRole::Customer))->firstOrFail();
        $foreign = Order::query()->where('customer_id', '!=', $customer->id)->firstOrFail();

        $this->actingAs($customer)->get(route('orders.show', $foreign))->assertNotFound();
    }

    public function test_a_customer_can_post_reply_and_report_in_the_forum(): void
    {
        $customer = User::query()->where('email', LoginController::demoEmail(UserRole::Customer))->firstOrFail();
        $this->actingAs($customer);

        $this->post(route('forum.store'), [
            'title' => '¿Dónde reciclo aceite usado?',
            'forum_category_id' => 1,
            'body' => 'Junté dos botellas de aceite de fritura y no sé a dónde llevarlas.',
        ])->assertRedirect();

        $topic = $customer->forumTopics()->latest('id')->firstOrFail();

        $this->post(route('forum.replies.store', $topic), ['body' => 'En los Puntos Verdes lo reciben.'])->assertRedirect();
        $this->get(route('forum.show', $topic))->assertOk()->assertSee('En los Puntos Verdes lo reciben.');

        $reports = ForumReport::query()->count();
        $this->post(route('forum.reports.store'), ['type' => 'topic', 'id' => $topic->id, 'reason' => 'spam'])->assertRedirect();
        $this->assertSame($reports + 1, ForumReport::query()->count());
    }
}
