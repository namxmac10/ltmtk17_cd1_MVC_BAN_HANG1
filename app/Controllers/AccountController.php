<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Order;

final class AccountController extends Controller
{
    public function orders(): void
    {
        $user = Auth::requireLogin();
        $this->render('account/orders', ['title' => 'Đơn hàng của tôi', 'orders' => Order::forUser((int) $user['id'])]);
    }

    public function show(int $id): void
    {
        $user = Auth::requireLogin();
        $order = Order::find($id);
        if (!$order || (int) $order['user_id'] !== (int) $user['id']) {
            abort(404);
        }
        $this->render('account/show', ['title' => 'Đơn hàng #' . $id, 'order' => $order]);
    }
}
