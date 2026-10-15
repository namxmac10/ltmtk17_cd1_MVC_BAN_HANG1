<div class="page-heading"><div><span class="section-overline">CHI TIẾT ĐẶT HÀNG</span><h1>Đơn #<?= (int) $order['id'] ?><span class="accent-period">.</span></h1><p>Cảm ơn bạn đã chọn Nova Store.</p></div><a class="text-link" href="<?= h(url('/orders')) ?>">← Danh sách đơn</a></div>
<div class="order-detail-grid"><?php require dirname(__DIR__) . '/partials/order-detail.php'; ?></div>
