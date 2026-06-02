<?php
require_once __DIR__ . '/../core/model.php';
require_once __DIR__ . '/../config/database.php';

class User extends Model {
    protected $table = 'users';
    
    public static function findByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ?";
        return Database::fetchOne($sql, [$email]);
    }
    
    public static function createWithPassword($data) {
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (name, email, password, age, role) 
                VALUES (?, ?, ?, ?, ?) RETURNING id";
        
        $params = [
            $data['name'],
            $data['email'],
            $hashedPassword,
            $data['age'] ?? null,
            $data['role'] ?? 'Мотоциклист'
        ];
        
        $stmt = Database::query($sql, $params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['id'];
    }
    
    public static function authenticate($email, $password) {
        $user = self::findByEmail($email);
        
        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']); 
            return $user;
        }
        
        return null;
    }
    
    public static function getAllUsers() {
        $sql = "SELECT id, name, email, age, role, created_at FROM users ORDER BY id";
        return Database::fetchAll($sql);
    }
    
    public static function getUserById($id) {
        $sql = "SELECT id, name, email, age, role, created_at FROM users WHERE id = ?";
        return Database::fetchOne($sql, [$id]);
    }
}
?>