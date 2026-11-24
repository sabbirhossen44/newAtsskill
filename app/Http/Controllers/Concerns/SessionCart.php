<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Collection;
use App\Http\Controllers\Concerns\SessionCartItem;

/**
 * Session-backed replacement for the old anayarojo/shoppingcart package
 * (Gloudemans\Shoppingcart\Facades\Cart), which is not in the new build.
 * Same public behaviour as the old cart: one row per course, keyed by a
 * rowId, options carry image/slug/real_price/discount_price.
 */
trait SessionCart
{
    protected function cartItems(): array
    {
        return session()->get('cart', []);
    }

    protected function cartContent(): Collection
    {
        return collect($this->cartItems())->map(
            fn (array $item, string $rowId) => new SessionCartItem($rowId, $item)
        )->values();
    }

    protected function cartCount(): int
    {
        return count($this->cartItems());
    }

    protected function cartAdd(array $data): SessionCartItem
    {
        $rowId = md5((string) ($data['id'] ?? '').serialize($data['options'] ?? []));
        $items = $this->cartItems();
        $items[$rowId] = $data;
        session()->put('cart', $items);

        return new SessionCartItem($rowId, $data);
    }

    protected function cartGet(string $rowId): ?SessionCartItem
    {
        $items = $this->cartItems();

        return isset($items[$rowId]) ? new SessionCartItem($rowId, $items[$rowId]) : null;
    }

    protected function cartRemove(string $rowId): void
    {
        $items = $this->cartItems();
        unset($items[$rowId]);
        session()->put('cart', $items);
    }

    protected function cartDestroy(): void
    {
        session()->forget('cart');
    }
}
