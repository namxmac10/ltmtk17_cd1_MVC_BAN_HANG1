<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Cart;
use App\Models\Order;
use DomainException;

final class CheckoutController extends Controller
{
    public function index(): void
    {
        $user = Auth::requireLogin();
        $cart = Cart::details();
        if (!$cart['lines']) {
            flash('error', 'Vui lòng thêm sản phẩm trước khi thanh toán.');
            redirect('/cart');
        }
        $this->render('checkout/index', ['title' => 'Thanh toán', 'user' => $user, 'cart' => $cart]);
    }

    public function place(): void
    {
        $user = Auth::requireLogin();
        $name = trim(postText('customer_name'));
        $phone = trim(postText('phone'));
        $address = trim(postText('address'));
        $method = postText('payment_method', 'cod');
        if ($name === '' || strlen($name) > 120 || !preg_match('/^[0-9+().\- ]{8,30}$/', $phone) || strlen($address) < 10 || strlen($address) > 500 || !in_array($method, ['cod', 'demo'], true)) {
            flash('error', 'Vui lòng điền đầy đủ thông tin nhận hàng hợp lệ.');
            redirect('/checkout');
        }
        try {
            $orderId = Order::place((int) $user['id'], $name, $phone, $address, $method, Cart::items());
        } catch (DomainException $error) {
            flash('error', $error->getMessage());
            redirect('/cart');
        }
        Cart::clear();
        flash('success', 'Đặt hàng thành công. Mã đơn #' . $orderId . '.');
        redirect('/orders/' . $orderId);
    }
}
