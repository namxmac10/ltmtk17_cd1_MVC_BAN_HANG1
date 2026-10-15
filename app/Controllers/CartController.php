<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Cart;
use App\Models\Product;

final class CartController extends Controller
{
    public function index(): void
    {
        $this->render('cart/index', ['title' => 'Giỏ hàng', 'cart' => Cart::details()]);
    }

    public function add(): void
    {
        $id = $this->positiveId($_POST['product_id'] ?? null);
        $quantity = $this->positiveId($_POST['quantity'] ?? 1);
        $product = Product::find($id);
        if (!$product || $quantity === 0 || $quantity > 20) {
            flash('error', 'Sản phẩm hoặc số lượng không hợp lệ.');
            redirect('/cart');
        }
        $current = Cart::items()[$id] ?? 0;
        if ($quantity + $current > 20 || $quantity + $current > (int) $product['stock']) {
            flash('error', 'Số lượng vượt quá tồn kho hoặc giới hạn 20 sản phẩm.');
            redirect('/products/' . $id);
        }
        Cart::set($id, $current + $quantity);
        flash('success', 'Đã thêm sản phẩm vào giỏ hàng.');
        redirect('/cart');
    }

    public function update(): void
    {
        $id = $this->positiveId($_POST['product_id'] ?? null);
        $qty = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 20]]);
        if (!$id || $qty === false) {
            flash('error', 'Số lượng không hợp lệ.');
            redirect('/cart');
        }
        $product = Product::find($id);
        if ($qty > 0 && (!$product || $qty > (int) $product['stock'])) {
            flash('error', 'Số lượng lớn hơn tồn kho hiện có.');
            redirect('/cart');
        }
        Cart::set($id, (int) $qty);
        flash('success', 'Giỏ hàng đã được cập nhật.');
        redirect('/cart');
    }
}
