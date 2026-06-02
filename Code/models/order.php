<?php
require_once __DIR__ . '/../core/model.php';
require_once __DIR__ . '/../config/database.php';

class Order extends Model {
    protected $table = 'orders';
    public static function createOrder($userId, $orderTotal, $orderItems) {
        $userIdInt = (int)$userId;
        $totalAmount = (float)$orderTotal;
        if ($totalAmount < 0) {
            throw new Exception("Total amount cannot be negative");
        }
        if (empty($orderItems)) {
            throw new Exception("Cannot create order with empty items");
        }
        
        try {
            
            $sql = "INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending') RETURNING id";
            $stmt = Database::query($sql, [$userIdInt, $totalAmount]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result || !isset($result['id'])) {
                throw new Exception("Failed to create order: no ID returned");
            }
            
            $orderId = (int)$result['id'];
            
            
            foreach ($orderItems as $item) {
                if (!isset($item['product_id']) || !isset($item['quantity']) || !isset($item['price'])) {
                    throw new Exception("Invalid item data: missing product_id, quantity, or price");
                }
                
                $sql = "INSERT INTO order_items (order_id, product_id, quantity, price_at_time) 
                        VALUES (?, ?, ?, ?)";
                
                Database::execute($sql, [
                    $orderId,
                    (int)$item['product_id'],
                    (int)$item['quantity'],
                    (float)$item['price']
                ]);
            }
            
            return $orderId;
            
        } catch (PDOException $e) {
            error_log("Order creation error: " . $e->getMessage());
            throw new Exception("Database error while creating order: " . $e->getMessage());
        }
    }
    public static function getUserOrders($userId) {
        $userIdInt = (int)$userId;
        
        $sql = "SELECT o.*, 
                (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count
                FROM orders o 
                WHERE o.user_id = ? 
                ORDER BY o.created_at DESC";
        
        return Database::fetchAll($sql, [$userIdInt]);
    }
    public static function getOrderWithItems($orderId) {
        $orderIdInt = (int)$orderId;
        

        $sql = "SELECT * FROM orders WHERE id = ?";
        $order = Database::fetchOne($sql, [$orderIdInt]);
        
        if (!$order) {
            return null;
        }
        
        $sql = "SELECT oi.*, p.name, p.brand, p.image 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?";
        $order['items'] = Database::fetchAll($sql, [$orderIdInt]);
        
        return $order;
    }
}
?>