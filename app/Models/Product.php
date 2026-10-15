<?php
namespace App\Models;

use App\Core\Database;
use PDO;

final class Product
{
    public static function all(string $search = '', string $category = ''): array
    {
        $where = [];
        $params = [];
        if ($search !== '') {
            $where[] = '(name LIKE :name OR description LIKE :description)';
            $params['name'] = '%' . $search . '%';
            $params['description'] = '%' . $search . '%';
        }
        if ($category !== '') {
            $where[] = 'category = :category';
            $params['category'] = $category;
        }
        $sql = 'SELECT * FROM products' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY id DESC';
        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll();
    }

    public static function categories(): array
    {
        return Database::connection()->query('SELECT DISTINCT category FROM products ORDER BY category')->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function find(int $id): ?array
    {
        $statement = Database::connection()->prepare('SELECT * FROM products WHERE id = ?');
        $statement->execute([$id]);
        return $statement->fetch() ?: null;
    }

    public static function save(array $data, ?int $id = null): void
    {
        $db = Database::connection();
        $values = [$data['name'], $data['category'], $data['description'], $data['price'], $data['stock'], $data['emoji'], $data['image_url']];
        if ($id === null) {
            $db->prepare('INSERT INTO products (name, category, description, price, stock, emoji, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)')->execute($values);
            return;
        }
        $values[] = $id;
        $db->prepare('UPDATE products SET name = ?, category = ?, description = ?, price = ?, stock = ?, emoji = ?, image_url = ? WHERE id = ?')->execute($values);
    }

    public static function delete(int $id): void
    {
        Database::connection()->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
    }

    public static function count(): int
    {
        return (int) Database::connection()->query('SELECT COUNT(*) FROM products')->fetchColumn();
    }
}
