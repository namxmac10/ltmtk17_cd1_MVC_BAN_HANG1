<?php
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

$GLOBALS['config'] = require dirname(__DIR__) . '/config/config.php';

function config(string $key, mixed $default = null): mixed
{
    $value = $GLOBALS['config'];
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}

function postText(string $key, string $default = ''): string
{
    $value = $_POST[$key] ?? $default;
    return is_string($value) ? $value : $default;
}

function queryText(string $key): string
{
    $value = $_GET[$key] ?? '';
    return is_string($value) ? $value : '';
}

function h(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function money(int|string $amount): string
{
    return number_format((int) $amount, 0, ',', '.') . ' ₫';
}

function basePath(): string
{
    $directory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    return $directory === '/' || $directory === '.' ? '' : rtrim($directory, '/');
}

function routePath(): string
{
    $requestPath = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
    $base = basePath();
    if ($base !== '') {
        if ($requestPath === $base || $requestPath === $base . '/') {
            return '/';
        }
        if (!str_starts_with($requestPath, $base . '/')) {
            abort(404);
        }
        $requestPath = substr($requestPath, strlen($base));
    }
    return $requestPath === '/index.php' ? '/' : $requestPath;
}

function url(string $path = ''): string
{
    return basePath() . '/' . ltrim($path, '/');
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function takeFlash(): ?array
{
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $message;
}

function redirect(string $path): never
{
    header('Location: ' . url($path), true, 303);
    exit;
}

function abort(int $status = 404): never
{
    http_response_code($status);
    $labels = [400 => 'Phiên biểu mẫu không hợp lệ', 403 => 'Không có quyền truy cập', 404 => 'Không tìm thấy trang'];
    $label = $labels[$status] ?? 'Đã có lỗi xảy ra';
    echo '<!doctype html><html lang="vi"><meta charset="utf-8"><title>' . h($label) . '</title><body style="font:16px system-ui;margin:10vh auto;max-width:560px;padding:24px"><h1>' . h($status) . '</h1><p>' . h($label) . '</p><a href="' . h(url('/')) . '">Về trang chủ</a></body></html>';
    exit;
}

function orderStatus(string $status): string
{
    return [
        'pending' => 'Chờ xử lý',
        'processing' => 'Đang xử lý',
        'completed' => 'Hoàn tất',
        'cancelled' => 'Đã hủy',
    ][$status] ?? $status;
}

function paymentStatus(string $status): string
{
    return $status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán';
}
