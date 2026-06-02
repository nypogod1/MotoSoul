<?php
class Logger {
    
    public static function write($login, $action) {
        $dir = __DIR__ . '/../logs';
        $file = $dir . '/auth.log';
        
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $time = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $line = $time . " | ip=" . $ip . " | login=" . $login . " | action=" . $action . PHP_EOL;
        
        file_put_contents($file, $line, FILE_APPEND);
    }
}
?>