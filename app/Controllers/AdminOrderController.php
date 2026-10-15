<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use DomainException;

final class AdminOrderController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $this->render('admin/orders/index', ['title' => 'Quản lý đơn hàng', 'orders' => Order::all()]);
    }

    public function create(): void
    {
        Auth::requireAdmin();
        $this->render('admin/orders/create', [
            'title' => 'Tạo đơn thủ công',
            'customers' => User::customers(),
            'products' => Product::all(),
        ]);
    }

    public function store(): void
    {
        Auth::requireAdmin();
        $userId = $this->positiveId($_POST['user_id'] ?? null);
        $productId = $this->positiveId($_POST['product_id'] ?? null);
        $quantity = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 20]]);
        $name = trim(postText('customer_name'));
        $phone = trim(postText('phone'));
        $address = trim(postText('address'));
        $method = postText('payment_method', 'cod');
        $customer = User::find($userId);
        if (!$customer || $customer['role'] !== 'customer' || !$productId || $quantity === false || $name === '' || strlen($name) > 120 || !preg_match('/^[0-9+().\- ]{8,30}$/', $phone) || strlen($address) < 10 || strlen($address) > 500 || !in_array($method, ['cod', 'demo'], true)) {
            flash('error', 'Thông tin tạo đơn chưa hợp lệ.');
            redirect('/admin/orders/create');
        }
        try {
            $id = Order::place($userId, $name, $phone, $address, $method, [$productId => $quantity]);
        } catch (DomainException $error) {
            flash('error', $error->getMessage());
            redirect('/admin/orders/create');
        }
        flash('success', 'Đã tạo đơn hàng #' . $id . '.');
        redirect('/admin/orders/' . $id);
    }

    public function show(int $id): void
    {
        Auth::requireAdmin();
        $order = Order::find($id);
        if (!$order) {
            abort();
        }
        $this->render('admin/orders/show', ['title' => 'Quản lý đơn #' . $id, 'order' => $order]);
    }

    public function update(int $id): void
    {
        Auth::requireAdmin();
        $data = [
            'customer_name' => trim(postText('customer_name')),
            'phone' => trim(postText('phone')),
            'address' => trim(postText('address')),
            'status' => postText('status'),
            'payment_status' => postText('payment_status'),
        ];
        if ($data['customer_name'] === '' || strlen($data['customer_name']) > 120 || !preg_match('/^[0-9+().\- ]{8,30}$/', $data['phone']) || strlen($data['address']) < 10 || strlen($data['address']) > 500 || !in_array($data['status'], ['pending', 'processing', 'completed', 'cancelled'], true) || !in_array($data['payment_status'], ['paid', 'unpaid'], true)) {
            flash('error', 'Thông tin đơn hàng không hợp lệ.');
            redirect('/admin/orders/' . $id);
        }
        try {
            Order::update($id, $data);
        } catch (DomainException $error) {
            flash('error', $error->getMessage());
            redirect('/admin/orders/' . $id);
        }
        flash('success', 'Đã cập nhật đơn hàng và đồng bộ tồn kho.');
        redirect('/admin/orders/' . $id);
    }

    public function delete(int $id): void
    {
        Auth::requireAdmin();
        try {
            Order::delete($id);
        } catch (DomainException $error) {
            flash('error', $error->getMessage());
            redirect('/admin/orders');
        }
        flash('success', 'Đã xóa đơn hàng và hoàn tồn kho khi cần.');
        redirect('/admin/orders');
    }
}
