<?php
require_once __DIR__ . '/../core/model.php';
require_once __DIR__ . '/../config/database.php';

class Forum extends Model {
    protected $table = 'forum_threads';
    
    public static function getAllThreads() {
        $sql = "SELECT t.*, COUNT(c.id) as comments_count 
                FROM forum_threads t 
                LEFT JOIN forum_comments c ON t.id = c.thread_id 
                GROUP BY t.id 
                ORDER BY t.created_at DESC";
        
        return Database::fetchAll($sql);
    }
    
    public static function getThreadsByCategory($category) {
        $sql = "SELECT t.*, COUNT(c.id) as comments_count 
                FROM forum_threads t 
                LEFT JOIN forum_comments c ON t.id = c.thread_id 
                WHERE t.category = ? 
                GROUP BY t.id 
                ORDER BY t.created_at DESC";
        
        return Database::fetchAll($sql, [$category]);
    }
    
    public static function getThreadById($id) {
        $sql = "SELECT t.*, COUNT(c.id) as comments_count 
                FROM forum_threads t 
                LEFT JOIN forum_comments c ON t.id = c.thread_id 
                WHERE t.id = ? 
                GROUP BY t.id";
        
        return Database::fetchOne($sql, [$id]);
    }
    
    public static function createThread($data) {
        $sql = "INSERT INTO forum_threads (category, title, content, author_id, author_name) 
                VALUES (?, ?, ?, ?, ?) RETURNING id";
        
        $stmt = Database::query($sql, [
            $data['category'],
            $data['title'],
            $data['content'],
            $data['author_id'],
            $data['author_name']
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['id'];
    }
    
    public static function incrementViews($threadId) {
        $sql = "UPDATE forum_threads SET views = views + 1 WHERE id = ?";
        return Database::execute($sql, [$threadId]);
    }
    
    public static function incrementReplies($threadId) {
        $sql = "UPDATE forum_threads SET replies = replies + 1 WHERE id = ?";
        return Database::execute($sql, [$threadId]);
    }
    
    public static function getCommentsByThreadId($threadId) {
        $sql = "SELECT * FROM forum_comments 
                WHERE thread_id = ? 
                ORDER BY created_at ASC";
        
        return Database::fetchAll($sql, [$threadId]);
    }
    
    public static function addComment($data) {
        $sql = "INSERT INTO forum_comments (thread_id, user_id, user_name, content) 
                VALUES (?, ?, ?, ?) RETURNING id";
        
        $stmt = Database::query($sql, [
            $data['thread_id'],
            $data['user_id'],
            $data['user_name'],
            $data['content']
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['id'];
    }
    
    public static function getCommentById($id) {
        $sql = "SELECT * FROM forum_comments WHERE id = ?";
        return Database::fetchOne($sql, [$id]);
    }

    public static function deleteThreadByAuthor($threadId, $userId) {
        $thread = Database::fetchOne("SELECT id, author_id FROM forum_threads WHERE id = ?", [(int) $threadId]);
        if (!$thread || (int) $thread['author_id'] !== (int) $userId) {
            return false;
        }
        Database::execute("DELETE FROM forum_comments WHERE thread_id = ?", [(int) $threadId]);
        Database::execute("DELETE FROM forum_threads WHERE id = ?", [(int) $threadId]);
        return true;
    }
}
?>