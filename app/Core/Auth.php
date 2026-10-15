<?php
namespace App\Core;

use App\Models\User;

final class Auth
{
    private static ?array $current = null;
    private static bool $resolved = false;

    public static function user(): ?array
    {
        if (!self::$resolved) {
            self::$resolved = true;
            self::$current = isset($_SESSION['user_id']) ? User::find((int) $_SESSION['user_id']) : null;
            if (!self::$current) {
                unset($_SESSION['user_id']);
            }
        }
        return self::$current;
    }

    public static function login(int $userId): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        self::$current = null;
        self::$resolved = false;
    }

    public static function logout(): void
    {
        unset($_SESSION['user_id'], $_SESSION['cart']);
        session_regenerate_id(true);
        self::$current = null;
        self::$resolved = true;
    }

    public static function requireLogin(): array
    {
        $user = self::user();
        if (!$user) {
            flash('error', 'Vui lòng đăng nhập để tiếp tục.');
            redirect('/login');
        }
        return $user;
    }

    public static function requireAdmin(): array
    {
        $user = self::requireLogin();
        if ($user['role'] !== 'admin') {
            abort(403);
        }
        return $user;
    }
}
