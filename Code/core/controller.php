<?php
abstract class Controller {
    
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    protected function getInput() {
        $input = json_decode(file_get_contents('php://input'), true);
        

        if ($input === null && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $_POST;
        }
        
        return $input ?? [];
    }
    
    protected function getAuthUser() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user'] ?? null;
    }
    
    protected function requireAuth() {
        $user = $this->getAuthUser();
        if (!$user) {
            $this->json(['error' => 'Authentication required'], 401);
            return false;
        }
        return $user;
    }
}
?>