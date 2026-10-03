<?php

namespace App\Services;

use App\Models\Dish;
use App\Models\Restaurant;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;

/**
 * Session cart. It holds dishes of a single restaurant: adding a dish from
 * another one starts a new cart.
 */
class Cart
{
    private const KEY = 'cart';

    private ?Collection $lines = null;

    public function __construct(private Session $session) {}

    /**
     * @return bool true when the previous cart was replaced
     */
    public function add(Dish $dish, int $quantity = 1): bool
    {
        $cart = $this->raw();
        $replaced = $cart['restaurant_id'] !== null && $cart['restaurant_id'] !== $dish->restaurant_id;

        if ($replaced) {
            $cart['items'] = [];
        }

        $cart['restaurant_id'] = $dish->restaurant_id;
        $cart['items'][$dish->id] = min(20, ($cart['items'][$dish->id] ?? 0) + $quantity);

        $this->store($cart);

        return $replaced;
    }

    public function setQuantity(int $dishId, int $quantity): void
    {
        $cart = $this->raw();

        if ($quantity <= 0) {
            unset($cart['items'][$dishId]);
        } elseif (isset($cart['items'][$dishId])) {
            $cart['items'][$dishId] = min(20, $quantity);
        }

        $this->store($cart);
    }

    public function clear(): void
    {
        $this->session->forget(self::KEY);
        $this->lines = null;
    }

    /**
     * @return Collection<int, object{dish: Dish, quantity: int, total: int}>
     */
    public function lines(): Collection
    {
        if ($this->lines !== null) {
            return $this->lines;
        }

        $items = $this->raw()['items'];
        $dishes = Dish::query()->whereKey(array_keys($items))->get();

        return $this->lines = $dishes->map(fn (Dish $dish) => (object) [
            'dish' => $dish,
            'quantity' => $items[$dish->id],
            'total' => $dish->price * $items[$dish->id],
        ])->values();
    }

    public function restaurant(): ?Restaurant
    {
        $id = $this->raw()['restaurant_id'];

        return $id ? Restaurant::query()->find($id) : null;
    }

    public function count(): int
    {
        return array_sum($this->raw()['items']);
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    public function subtotal(): int
    {
        return $this->lines()->sum('total');
    }

    /**
     * @return array{restaurant_id: int|null, items: array<int, int>}
     */
    private function raw(): array
    {
        return $this->session->get(self::KEY, ['restaurant_id' => null, 'items' => []]);
    }

    private function store(array $cart): void
    {
        if ($cart['items'] === []) {
            $cart['restaurant_id'] = null;
        }

        $this->session->put(self::KEY, $cart);
        $this->lines = null;
    }
}
