<?php
require_once __DIR__ . '/../models/product.php';
require_once __DIR__ . '/../core/controller.php';

class ProductController extends Controller {
    
    public function getAll() {
        $products = Product::all();
        $this->json([
            'success' => true,
            'data' => $products
        ]);
    }
    
    public function getOne($params) {
        $id = $params['id'] ?? null;
        $product = Product::find($id);
        
        if (!$product) {
            $this->json(['error' => 'Product not found'], 404);
            return;
        }
        
        $this->json([
            'success' => true,
            'data' => $product
        ]);
    }
    
    public function getByCategory($params) {
        $category = urldecode($params['category'] ?? '');
        $products = Product::where("category = ?", [$category]);
        
        $this->json([
            'success' => true,
            'data' => $products
        ]);
    }
}
?>