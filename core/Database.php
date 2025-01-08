<?php
class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $config = require __DIR__ . '/../config/db.php';
        
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
        
        try {
            $this->pdo = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options']
            );
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function fetch($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function fetchAll($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function execute($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    public function commit() {
        return $this->pdo->commit();
    }

    public function rollBack() {
        return $this->pdo->rollBack();
    }
}
?>
