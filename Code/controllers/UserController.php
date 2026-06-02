<?php
require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/../core/controller.php';

class UserController extends Controller {
    
    public function getAll() {
        $users = User::getAllUsers();
        $this->json([
            'success' => true,
            'data' => $users
        ]);
    }
    
    public function getOne($params) {
        $id = $params['id'] ?? null;
        
        if (!$id) {
            $this->json(['error' => 'User ID is required'], 400);
            return;
        }
        
        $user = User::getUserById($id);
        
        if (!$user) {
            $this->json(['error' => 'User not found'], 404);
            return;
        }
        
        $this->json([
            'success' => true,
            'data' => $user
        ]);
    }

    public function create() {
        $input = $this->getInput();
        
        $required = ['name', 'email', 'password'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                $this->json(['error' => "Field '{$field}' is required"], 400);
                return;
            }
        }

        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $this->json(['error' => 'Invalid email format'], 400);
            return;
        }
        
        $existingUser = User::findByEmail($input['email']);
        if ($existingUser) {
            $this->json(['error' => 'Email already exists'], 409);
            return;
        }
        
        if (strlen($input['password']) < 6) {
            $this->json(['error' => 'Password must be at least 6 characters'], 400);
            return;
        }
        
        try {
            $userId = User::createWithPassword($input);
            $newUser = User::getUserById($userId);
            
            $this->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $newUser
            ], 201);
        } catch (Exception $e) {
            $this->json(['error' => 'Failed to create user: ' . $e->getMessage()], 500);
        }
    }
}
?>