<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

//CORS JSON
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../controllers/',
        __DIR__ . '/../models/',
        __DIR__ . '/../core/',
        __DIR__ . '/../middleware/'
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});


require_once __DIR__ . '/../core/router.php';
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../controllers/productcontroller.php';
require_once __DIR__ . '/../controllers/authcontroller.php';
require_once __DIR__ . '/../controllers/cartcontroller.php';
require_once __DIR__ . '/../controllers/forumcontroller.php';
require_once __DIR__ . '/../controllers/ordercontroller.php';

$router = new Router();
$router->add('GET', '/users', function($params) {
    $controller = new UserController();
    $controller->getAll();
});

$router->add('GET', '/users/:id', function($params) {
    $controller = new UserController();
    $controller->getOne($params);
});

$router->add('POST', '/users', function($params) {
    $controller = new UserController();
    $controller->create();
});

$router->add('POST', '/auth/login', function($params) {
    $controller = new AuthController();
    $controller->login();
});

$router->add('POST', '/auth/logout', function($params) {
    $controller = new AuthController();
    $controller->logout();
});

$router->add('GET', '/products', function($params) {
    $controller = new ProductController();
    $controller->getAll();
});

$router->add('GET', '/products/:id', function($params) {
    $controller = new ProductController();
    $controller->getOne($params);
});

$router->add('GET', '/products/category/:category', function($params) {
    $controller = new ProductController();
    $controller->getByCategory($params);
});

$router->add('GET', '/cart', function($params) {
    $controller = new CartController();
    $controller->getCart();
});

$router->add('POST', '/cart', function($params) {
    $controller = new CartController();
    $controller->addToCart();
});

$router->add('PUT', '/cart/:id', function($params) {
    $controller = new CartController();
    $controller->updateQuantity($params);
});

$router->add('DELETE', '/cart/:id', function($params) {
    $controller = new CartController();
    $controller->removeFromCart($params);
});

$router->add('POST', '/cart/checkout', function($params) {
    $controller = new CartController();
    $controller->checkout();
});

$router->add('GET', '/orders/my', function($params) {
    $controller = new OrderController();
    $controller->getMyOrders();
});


$router->add('GET', '/forum/threads', function($params) {
    $controller = new ForumController();
    $controller->getThreads();
});

$router->add('GET', '/forum/threads/:id', function($params) {
    $controller = new ForumController();
    $controller->getThread($params);
});

$router->add('POST', '/forum/threads', function($params) {
    $controller = new ForumController();
    $controller->createThread();
});

$router->add('DELETE', '/forum/threads/:id', function($params) {
    $controller = new ForumController();
    $controller->deleteThread($params);
});

$router->add('GET', '/forum/threads/:id/comments', function($params) {
    $controller = new ForumController();
    $controller->getComments($params);
});

$router->add('POST', '/forum/threads/:id/comments', function($params) {
    $controller = new ForumController();
    $controller->addComment($params);
});

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$uri = preg_replace('#^.*?/api(?:/index\.php)?#', '', $uri);
$uri = '/' . ltrim($uri, '/');
if ($uri === '/' || $uri === '') {
    $uri = '/';
}

try {
    $router->dispatch($method, $uri);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>