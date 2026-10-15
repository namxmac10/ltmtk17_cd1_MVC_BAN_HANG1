<article class="product-card">
    <a class="product-art art-<?= ((int) $product['id'] % 5) + 1 ?>" href="<?= h(url('/products/' . (int) $product['id'])) ?>">
        <?php if ($product['image_url']): ?><img src="<?= h($product['image_url']) ?>" alt="<?= h($product['name']) ?>" loading="lazy"><?php else: ?><span class="product-emoji"><?= h($product['emoji']) ?></span><?php endif; ?>
        <?php if ((int) $product['stock'] === 0): ?><span class="out-badge">Hết hàng</span><?php endif; ?>
    </a>
    <div class="product-card-content">
        <span class="eyebrow"><?= h($product['category']) ?></span>
        <a class="product-name" href="<?= h(url('/products/' . (int) $product['id'])) ?>"><?= h($product['name']) ?></a>
        <div class="product-foot"><strong class="price"><?= money($product['price']) ?></strong><span class="stock-text">Còn <?= (int) $product['stock'] ?></span></div>
        <form action="<?= h(url('/cart/add')) ?>" method="post">
            <input type="hidden" name="_csrf" value="<?= h(\App\Core\Csrf::token()) ?>">
            <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
            <input type="hidden" name="quantity" value="1">
            <button class="btn btn-add" type="submit" <?= (int) $product['stock'] < 1 ? 'disabled' : '' ?>>+ Thêm vào giỏ</button>
        </form>
    </div>
</article>
