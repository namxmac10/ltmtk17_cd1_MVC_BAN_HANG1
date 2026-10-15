<?php
namespace App\Models;

use App\Core\Database;

final class User
{
    public static function find(int $id): ?array
    {
        $statement = Database::connection()->prepare('SELECT * FROM users WHERE id = ?');
        $statement->execute([$id]);
        return $statement->fetch() ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $statement = Database::connection()->prepare('SELECT * FROM users WHERE email = ?');
        $statement->execute([$email]);
        return $statement->fetch() ?: null;
    }

    public static function all(): array
    {
        return Database::connection()->query('SELECT id, full_name, email, role, created_at FROM users ORDER BY id DESC')->fetchAll();
    }

    public static function customers(): array
    {
        return Database::connection()->query("SELECT id, full_name, email FROM users WHERE role = 'customer' ORDER BY full_name")->fetchAll();
    }

    public static function create(array $data): int
    {
        $db = Database::connection();
        $db->prepare('INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)')->execute([
            $data['full_name'], $data['email'], $data['password'], $data['role'],
        ]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $fields = ['full_name = ?', 'email = ?', 'role = ?'];
        $values = [$data['full_name'], $data['email'], $data['role']];
        if ($data['password'] !== '') {
            $fields[] = 'password = ?';
            $values[] = $data['password'];
        }
        $values[] = $id;
        Database::connection()->prepare('UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?')->execute($values);
    }

    public static function delete(int $id): void
    {
        Database::connection()->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
    }

    public static function count(): int
    {
        return (int) Database::connection()->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }
}
