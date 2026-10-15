<section class="hero">
    <div class="hero-copy">
        <span class="hero-kicker"><span class="pulse-dot"></span> BỘ SƯU TẬP ĐƯỢC YÊU THÍCH</span>
        <h1>Đồ tốt cho<br><em>ngày thật mới.</em></h1>
        <p>Khám phá những món đồ công nghệ và phong cách sống được chọn lọc cho nhịp sống hiện đại.</p>
        <a href="#products" class="btn btn-white">Khám phá ngay <span aria-hidden="true">↗</span></a>
    </div>
    <div class="hero-visual" aria-hidden="true"><span class="hero-orbit orbit-1"></span><span class="hero-orbit orbit-2"></span><span class="hero-disc">✦</span><span class="hero-glow"></span><span class="hero-caption">GOOD THINGS<br>EVERY DAY.</span></div>
</section>
<div class="benefit-strip"><span>✧ Chọn lọc chất lượng</span><span>▣ Mua sắm đơn giản</span><span>↺ Theo dõi đơn hàng dễ dàng</span></div>
<section class="section-space" id="products">
    <div class="section-heading"><div><span class="section-overline">KHÁM PHÁ NGAY</span><h2>Sản phẩm nổi bật<span class="accent-period">.</span></h2><p>Món đồ nhỏ, trải nghiệm lớn.</p></div><span class="results-count"><?= count($products) ?> sản phẩm</span></div>
    <form class="search-panel" method="get" action="<?= h(url('/')) ?>" aria-label="Tìm sản phẩm">
        <input name="q" value="<?= h($search) ?>" placeholder="Tìm tên hoặc mô tả sản phẩm..." maxlength="100" aria-label="Tìm kiếm">
        <select name="category" aria-label="Danh mục"><option value="">Tất cả danh mục</option><?php foreach ($categories as $item): ?><option value="<?= h($item) ?>" <?= $item === $category ? 'selected' : '' ?>><?= h($item) ?></option><?php endforeach; ?></select>
        <button type="submit" class="btn btn-primary">Tìm sản phẩm</button>
    </form>
    <?php if ($products): ?><div class="product-grid"><?php foreach ($products as $product): ?><?php require dirname(__DIR__) . '/partials/product-card.php'; ?><?php endforeach; ?></div><?php else: ?><div class="empty-state"><div class="empty-icon">⌕</div><h3>Chưa tìm thấy sản phẩm</h3><p>Thử từ khóa hoặc danh mục khác.</p><a href="<?= h(url('/')) ?>" class="btn btn-primary">Xem tất cả</a></div><?php endif; ?>
</section>
