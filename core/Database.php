<?php

class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        $config = require __DIR__ . '/../config/database.php';

        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";

        try {
            $this->pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        // Her getConnection çağrısında bağlantıyı kontrol et
        $this->reconnectIfNeeded();
        return $this->pdo;
    }

    /**
     * Bağlantının hala aktif olup olmadığını kontrol et ve gerekirse yeniden bağlan
     */
    private function reconnectIfNeeded()
    {
        try {
            // Basit bir ping sorgusu ile bağlantıyı test et
            if ($this->pdo === null) {
                $this->reconnect();
                return;
            }

            @$this->pdo->query('SELECT 1');
        } catch (PDOException $e) {
            // Bağlantı kopmuş, yeniden bağlan
            $this->reconnect();
        } catch (Exception $e) {
            // Herhangi bir hata, reconnect dene
            $this->reconnect();
        }
    }

    private function reconnect()
    {
        try {
            error_log("MySQL reconnecting...");
            $config = require __DIR__ . '/../config/database.php';
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";

            // Eski bağlantıyı temizle
            $this->pdo = null;

            // Yeni bağlantı oluştur
            $this->pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
            error_log("MySQL reconnected successfully");
        } catch (PDOException $e) {
            error_log("MySQL reconnect failed: " . $e->getMessage());
            throw $e;
        }
    }

    public function query($sql, $params = [])
    {
        try {
            // Her sorgudan önce bağlantıyı kontrol et
            $this->reconnectIfNeeded();

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database query error: " . $e->getMessage());
            throw $e;
        }
    }

    public function fetchAll($sql, $params = [])
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetch($sql, $params = [])
    {
        return $this->query($sql, $params)->fetch();
    }

    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }
}
