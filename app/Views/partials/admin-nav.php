<div class="admin-nav">
    <a class="<?= ($section ?? '') === 'dashboard' ? 'selected' : '' ?>" href="<?= h(url('/admin')) ?>">Tổng quan</a>
    <a class="<?= ($section ?? '') === 'products' ? 'selected' : '' ?>" href="<?= h(url('/admin/products')) ?>">Sản phẩm</a>
    <a class="<?= ($section ?? '') === 'orders' ? 'selected' : '' ?>" href="<?= h(url('/admin/orders')) ?>">Đơn hàng</a>
    <a class="<?= ($section ?? '') === 'users' ? 'selected' : '' ?>" href="<?= h(url('/admin/users')) ?>">Tài khoản</a>
</div>
