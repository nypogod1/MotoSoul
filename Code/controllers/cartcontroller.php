<?php
require_once __DIR__ . '/../models/cart.php';
require_once __DIR__ . '/../models/order.php';
require_once __DIR__ . '/../models/product.php';
require_once __DIR__ . '/../core/controller.php';

class CartController extends Controller {
    
    public function getCart() {
        $user = $this->requireAuth();
        if (!$user) return;
        
        try {
            $cartItems = Cart::getUserCart($user['id']);
            $total = Cart::calculateTotal($user['id']);
            
            $this->json([
                'success' => true,
                'data' => $cartItems,
                'total' => $total,
                'items_count' => count($cartItems)
            ]);
        } catch (Exception $e) {
            $this->json(['error' => 'Failed to fetch cart: ' . $e->getMessage()], 500);
        }
    }
    
    public function addToCart() {
        $user = $this->requireAuth();
        if (!$user) return;
        
        $input = $this->getInput();
        
        $productId = $input['product_id'] ?? null;
        $quantity = $input['quantity'] ?? 1;
        
        if (!$productId) {
            $this->json(['error' => 'Product ID is required'], 400);
            return;
        }
        $product = Product::find($productId);
        if (!$product) {
            $this->json(['error' => 'Product not found'], 404);
            return;
        }
    
        if ($product['stock'] < $quantity) {
            $this->json(['error' => 'Not enough stock'], 400);
            return;
        }
        
        try {

            $cartItem = Cart::addOrUpdate($user['id'], $productId, $quantity);
            
            $this->json([
                'success' => true,
                'message' => 'Product added to cart',
                'data' => $cartItem
            ], 201);
        } catch (Exception $e) {
            $this->json(['error' => 'Failed to add to cart: ' . $e->getMessage()], 500);
        }
    }
    public function updateQuantity($params) {
        $user = $this->requireAuth();
        if (!$user) return;
        
        $cartItemId = $params['id'] ?? null;
        
        if (!$cartItemId) {
            $this->json(['error' => 'Cart item ID is required'], 400);
            return;
        }
        
        $input = $this->getInput();
        $quantity = $input['quantity'] ?? null;
        
        if ($quantity === null || $quantity < 1) {
            $this->json(['error' => 'Valid quantity is required'], 400);
            return;
        }
        
        try {

            $cartItem = Cart::find($cartItemId);
            if (!$cartItem || $cartItem['user_id'] != $user['id']) {
                $this->json(['error' => 'Cart item not found'], 404);
                return;
            }
            
            $product = Product::find($cartItem['product_id']);
            if ($product['stock'] < $quantity) {
                $this->json(['error' => 'Not enough stock'], 400);
                return;
            }
            
            $updated = Cart::updateQuantity($cartItemId, $quantity);
            
            $this->json([
                'success' => true,
                'message' => 'Cart updated'
            ]);
        } catch (Exception $e) {
            $this->json(['error' => 'Failed to update cart: ' . $e->getMessage()], 500);
        }
    }
    
    public function removeFromCart($params) {
        $user = $this->requireAuth();
        if (!$user) return;
        
        $cartItemId = $params['id'] ?? null;
        
        if (!$cartItemId) {
            $this->json(['error' => 'Cart item ID is required'], 400);
            return;
        }
        
        try {
            $cartItem = Cart::find($cartItemId);
            if (!$cartItem || $cartItem['user_id'] != $user['id']) {
                $this->json(['error' => 'Cart item not found'], 404);
                return;
            }
            
            Cart::delete($cartItemId);
            
            $this->json([
                'success' => true,
                'message' => 'Product removed from cart'
            ]);
        } catch (Exception $e) {
            $this->json(['error' => 'Failed to remove from cart: ' . $e->getMessage()], 500);
        }
    }
    
     public function checkout() {
        $user = $this->requireAuth();
        if (!$user) return;

        $input = $this->getInput();
        
        try {
            $preparedItems = [];
            $totalAmount = 0;

            // Заказ из корзины на фронтенде (передаётся в теле запроса)
            if (!empty($input['items']) && is_array($input['items'])) {
                foreach ($input['items'] as $item) {
                    if (!isset($item['product_id'], $item['quantity'], $item['price'])) {
                        $this->json(['error' => 'Invalid item in cart'], 400);
                        return;
                    }
                    $qty = (int) $item['quantity'];
                    $price = (float) $item['price'];
                    if ($qty < 1 || $price < 0) {
                        $this->json(['error' => 'Invalid quantity or price'], 400);
                        return;
                    }
                    $product = Product::find($item['product_id']);
                    if (!$product) {
                        $this->json(['error' => 'Product not found: ' . $item['product_id']], 404);
                        return;
                    }
                    $preparedItems[] = [
                        'product_id' => (int) $item['product_id'],
                        'quantity' => $qty,
                        'price' => $price
                    ];
                    $totalAmount += $price * $qty;
                }
            } else {
                $cartItems = Cart::getUserCart($user['id']);
            
                if (empty($cartItems)) {
                    $this->json(['error' => 'Cart is empty'], 400);
                    return;
                }
            
                $totalAmount = Cart::calculateTotal($user['id']);
            
                foreach ($cartItems as $item) {
                    $preparedItems[] = [
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price']
                    ];
                }
            }

            if (empty($preparedItems)) {
                $this->json(['error' => 'Cart is empty'], 400);
                return;
            }
            
            $orderId = Order::createOrder($user['id'], $totalAmount, $preparedItems);
            Cart::clearUserCart($user['id']);
            

            foreach ($preparedItems as $item) {
                Product::decreaseStock($item['product_id'], $item['quantity']);
            }
            
            $this->json([
                'success' => true,
                'message' => 'Order created successfully',
                'order_id' => $orderId,
                'total' => $totalAmount
            ]);
            
        } catch (Exception $e) {
            $this->json(['error' => 'Checkout failed: ' . $e->getMessage()], 500);
        }
    }
}
?>