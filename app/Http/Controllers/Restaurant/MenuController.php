<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Dish;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * M5: the restaurant loads and edits its menu: dishes, prices, availability.
 */
class MenuController extends Controller
{
    public function index(Request $request): View
    {
        $restaurant = $request->user()->restaurant;

        return view('restaurant.menu.index', [
            'restaurant' => $restaurant,
            'sections' => $restaurant->dishes()
                ->orderBy('name')
                ->get()
                ->groupBy('section')
                ->sortBy(fn ($dishes, string $section) => array_search($section, Dish::SECTIONS, true)),
        ]);
    }

    public function create(): View
    {
        return view('restaurant.menu.form', ['dish' => new Dish(['is_available' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $dish = $request->user()->restaurant->dishes()->create($this->validated($request));

        return redirect()->route('restaurant.menu.index')->with('status', "Agregaste {$dish->name} al menú.");
    }

    public function edit(Request $request, Dish $dish): View
    {
        $this->ensureOwn($request, $dish);

        return view('restaurant.menu.form', ['dish' => $dish]);
    }

    public function update(Request $request, Dish $dish): RedirectResponse
    {
        $this->ensureOwn($request, $dish);

        $dish->update($this->validated($request));

        return redirect()->route('restaurant.menu.index')->with('status', "Guardaste los cambios de {$dish->name}.");
    }

    public function toggle(Request $request, Dish $dish): RedirectResponse
    {
        $this->ensureOwn($request, $dish);

        $dish->update(['is_available' => ! $dish->is_available]);

        return back()->with('status', $dish->is_available
            ? "{$dish->name} vuelve a estar disponible."
            : "Marcaste {$dish->name} como sin stock.");
    }

    public function destroy(Request $request, Dish $dish): RedirectResponse
    {
        $this->ensureOwn($request, $dish);

        $dish->delete();

        return redirect()->route('restaurant.menu.index')->with('status', "Quitaste {$dish->name} del menú.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'section' => ['required', Rule::in(Dish::SECTIONS)],
            'name' => ['required', 'string', 'max:80'],
            'description' => ['required', 'string', 'max:200'],
            'price' => ['required', 'integer', 'min:100', 'max:500000'],
            'tags' => ['nullable', 'array'],
            'tags.*' => [Rule::in(Dish::TAGS)],
        ], [
            'price.integer' => 'Escribí el precio en pesos, sin puntos ni centavos.',
            'price.min' => 'El precio tiene que ser de al menos $ 100.',
        ]);

        return [...$data, 'tags' => $data['tags'] ?? [], 'is_available' => $request->boolean('is_available')];
    }

    private function ensureOwn(Request $request, Dish $dish): void
    {
        abort_unless($dish->restaurant_id === $request->user()->restaurant->id, 404);
    }
}
