<?php
$currentUser = \App\Core\Auth::user();
$notice = takeFlash();
$currentPath = routePath();
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f172a">
    <title><?= h($title ?? config('app_name')) ?> | <?= h(config('app_name')) ?></title>
    <link rel="stylesheet" href="<?= h(url('/assets/css/style.css')) ?>">
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <a class="brand" href="<?= h(url('/')) ?>"><span class="brand-logo">N</span><span>NOVA<span class="brand-light">STORE</span></span></a>
            <nav class="main-nav" aria-label="Điều hướng chính">
                <a class="<?= $currentPath === '/' ? 'nav-active' : '' ?>" href="<?= h(url('/')) ?>">Cửa hàng</a>
                <?php if ($currentUser): ?>
                    <a class="<?= $currentPath === '/orders' ? 'nav-active' : '' ?>" href="<?= h(url('/orders')) ?>">Đơn hàng</a>
                    <?php if ($currentUser['role'] === 'admin'): ?><a class="<?= str_starts_with($currentPath ?: '', '/admin') ? 'nav-active' : '' ?>" href="<?= h(url('/admin')) ?>">Quản trị</a><?php endif; ?>
                <?php endif; ?>
            </nav>
            <div class="header-actions">
                <a class="cart-link" href="<?= h(url('/cart')) ?>" aria-label="Giỏ hàng"><svg class="cart-icon" aria-hidden="true" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/></svg><span>Giỏ hàng</span><span class="cart-bubble"><?= \App\Models\Cart::count() ?></span></a>
                <?php if ($currentUser): ?>
                    <span class="header-user" title="<?= h($currentUser['email']) ?>"><?= h($currentUser['full_name']) ?></span>
                    <form action="<?= h(url('/logout')) ?>" method="post" class="inline-form"><input type="hidden" name="_csrf" value="<?= h(\App\Core\Csrf::token()) ?>"><button type="submit" class="btn btn-quiet btn-sm">Thoát</button></form>
                <?php else: ?>
                    <a class="btn btn-quiet btn-sm" href="<?= h(url('/login')) ?>">Đăng nhập</a>
                    <a class="btn btn-primary btn-sm" href="<?= h(url('/register')) ?>">Đăng ký</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <main class="container main-content">
        <?php if ($notice): ?>
            <div class="alert alert-<?= $notice['type'] === 'success' ? 'success' : 'error' ?>" role="alert"><?= h($notice['message']) ?></div>
        <?php endif; ?>
        <?= $content ?>
    </main>
    <footer class="site-footer"><div class="container footer-inner"><div><strong>NOVA STORE</strong><span>Chọn kỹ. Dùng lâu. Sống tốt.</span></div><span>© <?= date('Y') ?> Nova Store · PHP MVC + MySQL</span></div></footer>
</body>
</html>
