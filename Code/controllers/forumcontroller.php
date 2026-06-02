<?php
require_once __DIR__ . '/../models/forum.php';
require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/../core/controller.php';

class ForumController extends Controller {

    public function getThreads() {
        $category = $_GET['category'] ?? null;
        
        try {
            if ($category && $category !== 'Все') {
                $threads = Forum::getThreadsByCategory($category);
            } else {
                $threads = Forum::getAllThreads();
            }
            
            $this->json([
                'success' => true,
                'data' => $threads,
                'count' => count($threads)
            ]);
        } catch (Exception $e) {
            $this->json(['error' => 'Failed to fetch threads: ' . $e->getMessage()], 500);
        }
    }
    
    public function getThread($params) {
        $threadId = $params['id'] ?? null;
        
        if (!$threadId) {
            $this->json(['error' => 'Thread ID is required'], 400);
            return;
        }
        
        try {

            Forum::incrementViews($threadId);
            
            $thread = Forum::getThreadById($threadId);
            
            if (!$thread) {
                $this->json(['error' => 'Thread not found'], 404);
                return;
            }
            
            $this->json([
                'success' => true,
                'data' => $thread
            ]);
        } catch (Exception $e) {
            $this->json(['error' => 'Failed to fetch thread: ' . $e->getMessage()], 500);
        }
    }
    public function createThread() {
        $user = $this->requireAuth();
        if (!$user) return;
        
        $input = $this->getInput();
        
        $category = $input['category'] ?? null;
        $title = $input['title'] ?? null;
        $content = $input['content'] ?? null;
        
        if (!$category || !$title || !$content) {
            $this->json(['error' => 'Category, title and content are required'], 400);
            return;
        }
        
        $validCategories = ['Маршруты', 'Техника', 'Экипировка', 'Мероприятия', 'Разное'];
        if (!in_array($category, $validCategories)) {
            $this->json(['error' => 'Invalid category'], 400);
            return;
        }
        
        try {
            $threadId = Forum::createThread([
                'category' => $category,
                'title' => $title,
                'content' => $content,
                'author_id' => $user['id'],
                'author_name' => $user['name']
            ]);
            
            $newThread = Forum::getThreadById($threadId);
            
            $this->json([
                'success' => true,
                'message' => 'Thread created successfully',
                'data' => $newThread
            ], 201);
        } catch (Exception $e) {
            $this->json(['error' => 'Failed to create thread: ' . $e->getMessage()], 500);
        }
    }
    public function getComments($params) {
        $threadId = $params['id'] ?? null;
        
        if (!$threadId) {
            $this->json(['error' => 'Thread ID is required'], 400);
            return;
        }
        
        try {
            $comments = Forum::getCommentsByThreadId($threadId);
            
            $this->json([
                'success' => true,
                'data' => $comments,
                'count' => count($comments)
            ]);
        } catch (Exception $e) {
            $this->json(['error' => 'Failed to fetch comments: ' . $e->getMessage()], 500);
        }
    }
    
    public function addComment($params) {
        $user = $this->requireAuth();
        if (!$user) return;
        
        $threadId = $params['id'] ?? null;
        
        if (!$threadId) {
            $this->json(['error' => 'Thread ID is required'], 400);
            return;
        }
        
        $input = $this->getInput();
        $content = $input['content'] ?? null;
        
        if (!$content) {
            $this->json(['error' => 'Comment content is required'], 400);
            return;
        }
        
        try {
            $thread = Forum::getThreadById($threadId);
            if (!$thread) {
                $this->json(['error' => 'Thread not found'], 404);
                return;
            }
            
            $commentId = Forum::addComment([
                'thread_id' => $threadId,
                'user_id' => $user['id'],
                'user_name' => $user['name'],
                'content' => $content
            ]);
            

            Forum::incrementReplies($threadId);
            
            $newComment = Forum::getCommentById($commentId);
            
            $this->json([
                'success' => true,
                'message' => 'Comment added successfully',
                'data' => $newComment
            ], 201);
        } catch (Exception $e) {
            $this->json(['error' => 'Failed to add comment: ' . $e->getMessage()], 500);
        }
    }

    public function deleteThread($params) {
        $user = $this->requireAuth();
        if (!$user) {
            return;
        }

        $threadId = $params['id'] ?? null;
        if (!$threadId) {
            $this->json(['error' => 'Thread ID is required'], 400);
            return;
        }

        try {
            $deleted = Forum::deleteThreadByAuthor($threadId, $user['id']);
            if (!$deleted) {
                $this->json(['error' => 'Тему нельзя удалить или она не найдена'], 403);
                return;
            }

            $this->json([
                'success' => true,
                'message' => 'Thread deleted'
            ]);
        } catch (Exception $e) {
            $this->json(['error' => 'Failed to delete thread: ' . $e->getMessage()], 500);
        }
    }
}
?>