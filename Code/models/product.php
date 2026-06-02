<?php
require_once __DIR__ . '/../core/model.php';
require_once __DIR__ . '/../config/database.php';

class Product extends Model {
    protected $table = 'products';
    
    public static function decreaseStock($productId, $quantity) {
        $sql = "UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?";
        return Database::execute($sql, [$quantity, $productId, $quantity]);
    }
    
    public static function getHotProducts() {
        $sql = "SELECT * FROM products WHERE hot = true LIMIT 8";
        return Database::fetchAll($sql);
    }
}
?>