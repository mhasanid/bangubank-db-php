<?php
namespace App\Databases\DbProcessor;

use PDO;
use PDOException;

class DatabaseConnection {

    const DSN = 'mysql:host=localhost;dbname=bangubank;port=3306;charset=utf8mb4'; 
    const USERNAME = 'root';
    const PASS = '';

    private static ?DatabaseConnection $instance = null;
    private PDO $pdo;

    private function __construct() {
       

        try {
            $this->pdo = new PDO(self::DSN, self::USERNAME, self::PASS);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            if ($e->getCode() == 1049) {
                $this->createDatabase();
                $this->pdo = new PDO(self::DSN, self::USERNAME, self::PASS);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } else {
                throw $e;
            }
        }
    }

    private function createDatabase() {
        try {
            $dsn = 'mysql:host=localhost;port=3306;charset=utf8mb4';
            $pdo = new PDO($dsn, self::USERNAME, self::PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $pdo->exec("CREATE DATABASE IF NOT EXISTS bangubank");
        } catch (PDOException $e) {
            echo "Failed to create database: " . $e->getMessage();
            exit;
        }
    }

    public static function getInstance(): DatabaseConnection {
        if (self::$instance === null) {
            self::$instance = new DatabaseConnection();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->pdo;
    }

    private function __clone() {}
    public function __wakeup() {}
}
