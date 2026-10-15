<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;

final class StoreController extends Controller
{
    public function index(): void
    {
        $search = trim(queryText('q'));
        $category = trim(queryText('category'));
        preg_match('/^.{0,100}/us', $search, $searchMatch);
        preg_match('/^.{0,80}/us', $category, $categoryMatch);
        $search = $searchMatch[0] ?? '';
        $category = $categoryMatch[0] ?? '';
        $this->render('store/index', [
            'title' => 'Khám phá sản phẩm',
            'products' => Product::all($search, $category),
            'categories' => Product::categories(),
            'search' => $search,
            'category' => $category,
        ]);
    }

    public function show(int $id): void
    {
        $product = Product::find($id);
        if (!$product) {
            abort();
        }
        $this->render('store/show', ['title' => $product['name'], 'product' => $product]);
    }
}
