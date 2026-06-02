<?php
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $config = [
            'host' => 'localhost',
            'port' => '5432',
            'db_name' => 'motosoul_db',
            'username' => 'postgres',
            'password' => '1612',
        ];

        if (file_exists(__DIR__ . '/database.local.php')) {
            $local = require __DIR__ . '/database.local.php';
            if (is_array($local)) {
                $config = array_merge($config, $local);
            }
        }

        try {
            $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['db_name']}";
            $this->connection = new PDO($dsn, $config['username'], $config['password']);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->exec("SET client_encoding TO 'UTF8'");
        } catch (PDOException $e) {
            error_log("Connection Error: " . $e->getMessage());
            throw new Exception('Не удалось подключиться к БД ' . $config['db_name'] . ': ' . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function resetInstance() {
        self::$instance = null;
    }

    public function getConnection() {
        return $this->connection;
    }

    public static function query($sql, $params = []) {
        $db = self::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetchAll($sql, $params = []) {
        $stmt = self::query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function fetchOne($sql, $params = []) {
        $stmt = self::query($sql, $params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function insert($sql, $params = []) {
        $stmt = self::query($sql, $params);
        return self::getInstance()->getConnection()->lastInsertId();
    }

    public static function execute($sql, $params = []) {
        $stmt = self::query($sql, $params);
        return $stmt->rowCount();
    }
}
?>
