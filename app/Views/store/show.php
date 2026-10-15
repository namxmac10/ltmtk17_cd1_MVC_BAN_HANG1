<div class="breadcrumb"><a href="<?= h(url('/')) ?>">Cửa hàng</a><span>/</span><span><?= h($product['category']) ?></span></div>
<div class="detail-grid">
    <div class="detail-art art-<?= ((int) $product['id'] % 5) + 1 ?>"><?php if ($product['image_url']): ?><img src="<?= h($product['image_url']) ?>" alt="<?= h($product['name']) ?>"><?php else: ?><span><?= h($product['emoji']) ?></span><?php endif; ?></div>
    <div class="detail-copy"><span class="eyebrow"><?= h($product['category']) ?></span><h1><?= h($product['name']) ?></h1><p class="detail-price"><?= money($product['price']) ?></p><p class="detail-description"><?= nl2br(h($product['description'])) ?></p><div class="inventory-note"><span class="pulse-dot"></span><?= (int) $product['stock'] > 0 ? 'Còn ' . (int) $product['stock'] . ' sản phẩm' : 'Tạm hết hàng' ?></div>
        <form class="purchase-form" method="post" action="<?= h(url('/cart/add')) ?>"><input type="hidden" name="_csrf" value="<?= h(\App\Core\Csrf::token()) ?>"><input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>"><label for="quantity">Số lượng</label><div class="purchase-controls"><input id="quantity" type="number" name="quantity" value="1" min="1" max="<?= min(20, (int) $product['stock']) ?>" required><button class="btn btn-primary" type="submit" <?= (int) $product['stock'] < 1 ? 'disabled' : '' ?>>+ Thêm vào giỏ</button></div></form>
        <div class="detail-benefits"><span>✓ Thông tin rõ ràng</span><span>✓ Kiểm tra trước khi đặt</span></div>
    </div>
</div>
