<?php
abstract class Model {
    protected $table;
    protected $primaryKey = 'id';
    
    public static function all() {
        $instance = new static();
        $sql = "SELECT * FROM {$instance->table}";
        return Database::fetchAll($sql);
    }
    
    public static function find($id) {
        $instance = new static();
        $sql = "SELECT * FROM {$instance->table} WHERE {$instance->primaryKey} = ?";
        return Database::fetchOne($sql, [$id]);
    }
    
    public static function where($conditions, $params = []) {
        $instance = new static();
        $sql = "SELECT * FROM {$instance->table} WHERE $conditions";
        return Database::fetchAll($sql, $params);
    }
    
    public static function create($data) {
        $instance = new static();
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $sql = "INSERT INTO {$instance->table} (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $placeholders) . ") RETURNING {$instance->primaryKey}";
        
        $params = array_values($data);
        $stmt = Database::query($sql, $params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result[$instance->primaryKey];
    }
    
    public static function update($id, $data) {
        $instance = new static();
        $sets = [];
        $params = [];
        
        foreach ($data as $field => $value) {
            $sets[] = "$field = ?";
            $params[] = $value;
        }
        
        $params[] = $id;
        $sql = "UPDATE {$instance->table} SET " . implode(', ', $sets) . 
               " WHERE {$instance->primaryKey} = ?";
        
        return Database::execute($sql, $params);
    }
    
    public static function delete($id) {
        $instance = new static();
        $sql = "DELETE FROM {$instance->table} WHERE {$instance->primaryKey} = ?";
        return Database::execute($sql, [$id]);
    }
}
?>