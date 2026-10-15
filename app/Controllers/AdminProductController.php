<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Product;

final class AdminProductController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $this->render('admin/products/index', ['title' => 'Quản lý sản phẩm', 'products' => Product::all()]);
    }

    public function create(): void
    {
        Auth::requireAdmin();
        $this->render('admin/products/form', ['title' => 'Thêm sản phẩm', 'product' => null]);
    }

    public function edit(int $id): void
    {
        Auth::requireAdmin();
        $product = Product::find($id);
        if (!$product) {
            abort();
        }
        $this->render('admin/products/form', ['title' => 'Sửa sản phẩm', 'product' => $product]);
    }

    public function store(): void
    {
        Auth::requireAdmin();
        $data = $this->input();
        Product::save($data);
        flash('success', 'Đã tạo sản phẩm.');
        redirect('/admin/products');
    }

    public function update(int $id): void
    {
        Auth::requireAdmin();
        if (!Product::find($id)) {
            abort();
        }
        Product::save($this->input(), $id);
        flash('success', 'Đã cập nhật sản phẩm.');
        redirect('/admin/products');
    }

    public function delete(int $id): void
    {
        Auth::requireAdmin();
        if (!Product::find($id)) {
            abort();
        }
        Product::delete($id);
        flash('success', 'Đã xóa sản phẩm. Dữ liệu sản phẩm trong đơn hàng cũ vẫn được giữ.');
        redirect('/admin/products');
    }

    private function input(): array
    {
        $name = trim(postText('name'));
        $category = trim(postText('category'));
        $description = trim(postText('description'));
        $price = filter_var($_POST['price'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1000, 'max_range' => 100000000]]);
        $stock = filter_var($_POST['stock'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1000000]]);
        $emoji = trim(postText('emoji')) ?: '📦';
        $image = trim(postText('image_url'));
        $scheme = strtolower((string) parse_url($image, PHP_URL_SCHEME));
        if ($name === '' || strlen($name) > 160 || $category === '' || strlen($category) > 80 || $description === '' || strlen($description) > 10000 || $price === false || $stock === false || strlen($emoji) > 32 || strlen($image) > 500 || ($image !== '' && (!filter_var($image, FILTER_VALIDATE_URL) || !in_array($scheme, ['https', 'http'], true)))) {
            flash('error', 'Thông tin sản phẩm không hợp lệ. Giá tối thiểu 1.000₫.');
            redirect('/admin/products');
        }
        return [
            'name' => $name,
            'category' => $category,
            'description' => $description,
            'price' => $price,
            'stock' => $stock,
            'emoji' => $emoji,
            'image_url' => $image === '' ? null : $image,
        ];
    }
}
