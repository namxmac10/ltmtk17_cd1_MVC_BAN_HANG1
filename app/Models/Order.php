<?php
namespace App\Models;

use App\Core\Database;
use DomainException;
use PDO;
use Throwable;

final class Order
{
    public static function place(int $userId, string $name, string $phone, string $address, string $paymentMethod, array $cart): int
    {
        if (!$cart) {
            throw new DomainException('Giỏ hàng đang trống.');
        }
        ksort($cart, SORT_NUMERIC);
        $db = Database::connection();
        $db->beginTransaction();
        try {
            $lines = [];
            $total = 0;
            $stockQuery = $db->prepare('SELECT id, name, price, stock FROM products WHERE id = ? FOR UPDATE');
            $reduceStock = $db->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');
            foreach ($cart as $id => $quantity) {
                $id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                $quantity = filter_var($quantity, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 20]]);
                if ($id === false || $quantity === false) {
                    throw new DomainException('Số lượng sản phẩm không hợp lệ.');
                }
                $stockQuery->execute([(int) $id]);
                $product = $stockQuery->fetch();
                if (!$product || (int) $product['stock'] < $quantity) {
                    throw new DomainException('Một sản phẩm đã hết hàng hoặc không đủ số lượng.');
                }
                $reduceStock->execute([$quantity, $id, $quantity]);
                if ($reduceStock->rowCount() !== 1) {
                    throw new DomainException('Số lượng tồn kho vừa thay đổi, vui lòng thử lại.');
                }
                $subtotal = (int) $product['price'] * $quantity;
                $total += $subtotal;
                $lines[] = [$id, $product['name'], $quantity, $product['price'], $subtotal];
            }
            $paymentStatus = $paymentMethod === 'demo' ? 'paid' : 'unpaid';
            $db->prepare('INSERT INTO orders (user_id, customer_name, phone, address, payment_method, payment_status, total) VALUES (?, ?, ?, ?, ?, ?, ?)')
                ->execute([$userId, $name, $phone, $address, $paymentMethod, $paymentStatus, $total]);
            $orderId = (int) $db->lastInsertId();
            $insertItem = $db->prepare('INSERT INTO order_items (order_id, product_id, product_name, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?, ?)');
            foreach ($lines as $line) {
                $insertItem->execute([$orderId, ...$line]);
            }
            $db->commit();
            return $orderId;
        } catch (Throwable $error) {
            $db->rollBack();
            throw $error;
        }
    }

    public static function find(int $id): ?array
    {
        $statement = Database::connection()->prepare('SELECT o.*, u.email AS user_email FROM orders o LEFT JOIN users u ON u.id = o.user_id WHERE o.id = ?');
        $statement->execute([$id]);
        $order = $statement->fetch();
        if (!$order) {
            return null;
        }
        $items = Database::connection()->prepare('SELECT * FROM order_items WHERE order_id = ? ORDER BY id');
        $items->execute([$id]);
        $order['items'] = $items->fetchAll();
        return $order;
    }

    public static function forUser(int $userId): array
    {
        $statement = Database::connection()->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC');
        $statement->execute([$userId]);
        return $statement->fetchAll();
    }

    public static function all(): array
    {
        return Database::connection()->query('SELECT o.*, u.email AS user_email FROM orders o LEFT JOIN users u ON u.id = o.user_id ORDER BY o.id DESC')->fetchAll();
    }

    public static function update(int $id, array $values): void
    {
        $db = Database::connection();
        $db->beginTransaction();
        try {
            $query = $db->prepare('SELECT status FROM orders WHERE id = ? FOR UPDATE');
            $query->execute([$id]);
            $order = $query->fetch();
            if (!$order) {
                throw new DomainException('Đơn hàng không tồn tại.');
            }
            if ($order['status'] !== $values['status']) {
                if ($values['status'] === 'cancelled') {
                    self::adjustStock($db, $id, true);
                } elseif ($order['status'] === 'cancelled') {
                    self::adjustStock($db, $id, false);
                }
            }
            $db->prepare('UPDATE orders SET customer_name = ?, phone = ?, address = ?, status = ?, payment_status = ? WHERE id = ?')
                ->execute([$values['customer_name'], $values['phone'], $values['address'], $values['status'], $values['payment_status'], $id]);
            $db->commit();
        } catch (Throwable $error) {
            $db->rollBack();
            throw $error;
        }
    }

    public static function delete(int $id): void
    {
        $db = Database::connection();
        $db->beginTransaction();
        try {
            $statement = $db->prepare('SELECT status FROM orders WHERE id = ? FOR UPDATE');
            $statement->execute([$id]);
            $order = $statement->fetch();
            if (!$order) {
                throw new DomainException('Đơn hàng không tồn tại.');
            }
            if ($order['status'] !== 'cancelled') {
                self::adjustStock($db, $id, true);
            }
            $db->prepare('DELETE FROM orders WHERE id = ?')->execute([$id]);
            $db->commit();
        } catch (Throwable $error) {
            $db->rollBack();
            throw $error;
        }
    }

    private static function adjustStock(PDO $db, int $orderId, bool $restore): void
    {
        $statement = $db->prepare('SELECT product_id, quantity FROM order_items WHERE order_id = ? ORDER BY product_id');
        $statement->execute([$orderId]);
        foreach ($statement->fetchAll() as $item) {
            if ($item['product_id'] === null) {
                if (!$restore) {
                    throw new DomainException('Không thể kích hoạt lại đơn có sản phẩm đã bị xóa.');
                }
                continue;
            }
            if ($restore) {
                $db->prepare('UPDATE products SET stock = stock + ? WHERE id = ?')->execute([$item['quantity'], $item['product_id']]);
            } else {
                $query = $db->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');
                $query->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
                if ($query->rowCount() !== 1) {
                    throw new DomainException('Không đủ tồn kho để kích hoạt lại đơn hàng.');
                }
            }
        }
    }

    public static function stats(): array
    {
        $db = Database::connection();
        return [
            'orders' => (int) $db->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
            'revenue' => (int) $db->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status <> 'cancelled'")->fetchColumn(),
            'pending' => (int) $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn(),
        ];
    }
}
