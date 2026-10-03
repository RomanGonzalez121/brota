<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Auth\LoginController;
use App\Models\Category;
use App\Models\ForumReport;
use App\Models\ForumTopic;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * M10 and M11: the administrator moderates the forum and adds or removes
 * restaurants and couriers.
 */
class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::query()->where('email', LoginController::demoEmail(UserRole::Admin))->firstOrFail();
        $this->actingAs($this->admin);
    }

    public function test_a_restaurant_is_added_with_the_account_of_who_runs_it(): void
    {
        $this->post(route('admin.restaurants.store'), [
            'restaurant_name' => 'Semillero Almagro',
            'category_id' => Category::query()->value('id'),
            'tagline' => 'Cocina de mercado, todo de estación.',
            'address' => 'Av. Medrano 950',
            'neighborhood' => 'Almagro',
            'owner_name' => 'Paula Rivas',
            'email' => 'paula@semillero.test',
            'password' => 'semillero123',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $restaurant = Restaurant::query()->where('slug', 'semillero-almagro')->firstOrFail();
        $this->assertTrue($restaurant->is_active);
        $this->assertSame(UserRole::Restaurant, $restaurant->owner->role);
        $this->get(route('admin.restaurants.index'))->assertOk()->assertSee('Semillero Almagro')->assertSee('Sin menú cargado');

        // The new owner can sign in and lands on their board.
        auth()->logout();
        $this->post(route('login.store'), ['email' => 'paula@semillero.test', 'password' => 'semillero123'])
            ->assertRedirect(route('restaurant.orders.index'));
    }

    public function test_a_restaurant_needs_an_email_nobody_else_uses(): void
    {
        $this->post(route('admin.restaurants.store'), [
            'restaurant_name' => 'Otro local',
            'category_id' => Category::query()->value('id'),
            'tagline' => 'Algo rico.',
            'address' => 'Calle 1',
            'neighborhood' => 'Centro',
            'owner_name' => 'Alguien',
            'email' => $this->admin->email,
            'password' => 'unaclave123',
        ])->assertSessionHasErrors('email');

        $this->assertFalse(Restaurant::query()->where('name', 'Otro local')->exists());
    }

    public function test_a_restaurant_taken_off_disappears_for_customers_and_comes_back(): void
    {
        $restaurant = Restaurant::query()->where('is_active', true)->has('dishes')->firstOrFail();
        $customer = User::query()->where('email', LoginController::demoEmail(UserRole::Customer))->firstOrFail();

        $this->patch(route('admin.restaurants.toggle', $restaurant))->assertRedirect();
        $this->assertFalse($restaurant->fresh()->is_active);
        $this->actingAs($customer)->get(route('restaurants.show', $restaurant))->assertNotFound();

        $this->actingAs($this->admin)->patch(route('admin.restaurants.toggle', $restaurant))->assertRedirect();
        $this->assertTrue($restaurant->fresh()->is_active);
        $this->actingAs($customer)->get(route('restaurants.show', $restaurant))->assertOk();
    }

    public function test_a_courier_is_added_and_can_be_taken_off_when_free(): void
    {
        $this->post(route('admin.couriers.store'), [
            'name' => 'Dana Ortiz',
            'email' => 'dana@brota.test',
            'phone' => '11 4444-2020',
            'vehicle' => 'Bicicleta',
            'password' => 'pedaleando1',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $courier = User::query()->where('email', 'dana@brota.test')->firstOrFail();
        $this->assertSame(UserRole::Courier, $courier->role);
        $this->get(route('admin.couriers.index'))->assertOk()->assertSee('Dana Ortiz');

        $this->patch(route('admin.couriers.toggle', $courier))->assertRedirect();
        $this->assertFalse($courier->fresh()->is_active);

        // Off Brotá means no way in.
        auth()->logout();
        $this->post(route('login.store'), ['email' => 'dana@brota.test', 'password' => 'pedaleando1'])->assertSessionHasErrors();
    }

    public function test_a_courier_with_orders_in_hand_cannot_be_taken_off(): void
    {
        $courier = User::query()->where('role', UserRole::Courier)
            ->whereHas('deliveries', fn ($orders) => $orders->whereIn('status', [OrderStatus::Preparing, OrderStatus::OnTheWay]))
            ->firstOrFail();

        $this->patch(route('admin.couriers.toggle', $courier))->assertRedirect()->assertSessionHas('status_tone', 'danger');
        $this->assertTrue($courier->fresh()->is_active);

        // Only couriers go through this door.
        $this->patch(route('admin.couriers.toggle', $this->admin))->assertNotFound();
    }

    public function test_removing_reported_content_settles_every_report_on_it(): void
    {
        $topic = ForumTopic::query()->has('reports', '>=', 2)->firstOrFail();
        $reports = $topic->reports()->where('status', 'pending')->get();
        $authorNotices = $topic->author->notifications()->count();

        $this->get(route('admin.reports.index'))->assertOk()->assertSee($topic->title)->assertSee('2 denuncias');

        $this->post(route('admin.reports.remove', $reports->first()))->assertRedirect();

        $this->assertNotNull($topic->fresh()->removed_at);
        $this->assertSame(0, $topic->reports()->where('status', 'pending')->count());
        $this->assertSame($reports->count(), $topic->reports()->where('status', 'removed')->where('reviewed_by', $this->admin->id)->count());
        $this->assertSame($authorNotices + 1, $topic->author->notifications()->count());

        // The same decision cannot be taken twice.
        $this->post(route('admin.reports.remove', $reports->first()))->assertStatus(409);

        // Customers no longer find it.
        $customer = User::query()->where('email', LoginController::demoEmail(UserRole::Customer))->firstOrFail();
        $this->actingAs($customer)->get(route('forum.show', $topic))->assertNotFound();

        // And it can be put back.
        $this->actingAs($this->admin)->post(route('admin.reports.restore', $reports->first()))->assertRedirect();
        $this->assertNull($topic->fresh()->removed_at);
        $this->actingAs($customer)->get(route('forum.show', $topic))->assertOk();
    }

    public function test_dismissing_a_report_leaves_the_content_published(): void
    {
        $report = ForumReport::query()->where('status', 'pending')->where('reportable_type', 'like', '%ForumReply')->firstOrFail();
        $reporterNotices = $report->reporter->notifications()->count();

        $this->post(route('admin.reports.dismiss', $report))->assertRedirect();

        $this->assertSame('dismissed', $report->fresh()->status);
        $this->assertNull($report->reportable->fresh()->removed_at);
        $this->assertSame($reporterNotices + 1, $report->reporter->notifications()->count());
    }

    public function test_only_the_administrator_gets_into_these_screens(): void
    {
        $restaurant = Restaurant::query()->firstOrFail();
        $owner = User::query()->where('email', LoginController::demoEmail(UserRole::Restaurant))->firstOrFail();

        $this->actingAs($owner)->patch(route('admin.restaurants.toggle', $restaurant))->assertRedirect(route('restaurant.orders.index'));
        $this->assertTrue($restaurant->fresh()->is_active);
    }
}
