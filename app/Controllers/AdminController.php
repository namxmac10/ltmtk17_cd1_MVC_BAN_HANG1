<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

final class AdminController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $this->render('admin/dashboard', [
            'title' => 'Tổng quan quản trị',
            'stats' => Order::stats(),
            'productsCount' => Product::count(),
            'usersCount' => User::count(),
            'orders' => array_slice(Order::all(), 0, 6),
        ]);
    }
}
