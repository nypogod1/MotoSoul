<?php
require_once __DIR__ . '/../core/model.php';
require_once __DIR__ . '/../config/database.php';

class Cart extends Model {
    protected $table = 'cart';
    
    public static function getUserCart($userId) {
        $sql = "SELECT c.*, p.name, p.brand, p.price, p.image, p.category 
                FROM cart c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = ? 
                ORDER BY c.added_at DESC";
        
        return Database::fetchAll($sql, [$userId]);
    }
    
    public static function addOrUpdate($userId, $productId, $quantity) {

        $sql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
        $existing = Database::fetchOne($sql, [$userId, $productId]);
        
        if ($existing) {

            $newQuantity = $existing['quantity'] + $quantity;
            $sql = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ? RETURNING *";
            $stmt = Database::query($sql, [$newQuantity, $userId, $productId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {

            $sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?) RETURNING *";
            $stmt = Database::query($sql, [$userId, $productId, $quantity]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
    
    public static function updateQuantity($cartItemId, $quantity) {
        $sql = "UPDATE cart SET quantity = ? WHERE id = ?";
        return Database::execute($sql, [$quantity, $cartItemId]);
    }
    
    public static function calculateTotal($userId) {
        $sql = "SELECT SUM(p.price * c.quantity) as total 
                FROM cart c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = ?";
        $result = Database::fetchOne($sql, [$userId]);
        return $result['total'] ?? 0;
    }
    
    public static function clearUserCart($userId) {
        $sql = "DELETE FROM cart WHERE user_id = ?";
        return Database::execute($sql, [$userId]);
    }
    
    public static function find($id) {
        $sql = "SELECT * FROM cart WHERE id = ?";
        return Database::fetchOne($sql, [$id]);
    }
    
    public static function delete($id) {
        $sql = "DELETE FROM cart WHERE id = ?";
        return Database::execute($sql, [$id]);
    }
}
?>