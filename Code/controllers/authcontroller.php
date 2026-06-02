<?php
require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/../core/controller.php';
require_once __DIR__ . '/../core/Logger.php';

class AuthController extends Controller {
    
    public function login() {
        $input = $this->getInput();
        
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';
        
        if (!$email || !$password) {
            $this->json(['error' => 'Email and password are required'], 400);
            return;
        }
        
        try {
            $user = User::authenticate($email, $password);
        } catch (Exception $e) {
            $this->json(['error' => 'Ошибка базы данных: ' . $e->getMessage()], 500);
            return;
        }
        
        if (!$user) {
            $existingUser = User::findByEmail($email);
            if (!$existingUser) {
                Logger::write($email, 'FAIL_LOGIN_NO_USER');
            } else {
                Logger::write($email, 'FAIL_LOGIN');
            }
            $this->json(['error' => 'Неверный email или пароль'], 401);
            return;
        }
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user'] = $user;
        
        Logger::write($email, 'SUCCESS_LOGIN');
        
        $this->json([
            'success' => true,
            'user' => $user
        ]);
    }
    
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $email = $_SESSION['user']['email'] ?? 'unknown';
        Logger::write($email, 'LOGOUT');
        
        session_destroy();
        
        $this->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}
?>