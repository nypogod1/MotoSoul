<?php
require_once __DIR__ . '/../models/order.php';
require_once __DIR__ . '/../core/controller.php';

class OrderController extends Controller {

    public function getMyOrders() {
        $user = $this->requireAuth();
        if (!$user) {
            return;
        }

        try {
            $orders = Order::getUserOrders($user['id']);
            foreach ($orders as &$order) {
                $full = Order::getOrderWithItems($order['id']);
                $order['items'] = $full['items'] ?? [];
            }
            unset($order);

            $this->json([
                'success' => true,
                'data' => $orders
            ]);
        } catch (Exception $e) {
            $this->json(['error' => 'Failed to fetch orders: ' . $e->getMessage()], 500);
        }
    }
}
?>
