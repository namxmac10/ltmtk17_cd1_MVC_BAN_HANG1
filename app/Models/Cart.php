<?php
namespace App\Models;

final class Cart
{
    public static function items(): array
    {
        $result = [];
        foreach ($_SESSION['cart'] ?? [] as $id => $quantity) {
            $id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $quantity = filter_var($quantity, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 20]]);
            if ($id !== false && $quantity !== false) {
                $result[(int) $id] = (int) $quantity;
            }
        }
        return $result;
    }

    public static function set(int $productId, int $quantity): void
    {
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$productId]);
        } else {
            $_SESSION['cart'][$productId] = min(20, $quantity);
        }
    }

    public static function count(): int
    {
        return array_sum(self::items());
    }

    public static function details(): array
    {
        $lines = [];
        $total = 0;
        foreach (self::items() as $id => $quantity) {
            $product = Product::find($id);
            if (!$product) {
                unset($_SESSION['cart'][$id]);
                continue;
            }
            $subtotal = (int) $product['price'] * $quantity;
            $total += $subtotal;
            $lines[] = ['product' => $product, 'quantity' => $quantity, 'subtotal' => $subtotal];
        }
        return ['lines' => $lines, 'total' => $total];
    }

    public static function clear(): void
    {
        unset($_SESSION['cart']);
    }
}
