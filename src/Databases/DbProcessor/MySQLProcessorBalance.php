<?php
namespace App\Databases\DbProcessor;

use App\Databases\Interfaces\BalanceInterface;
use App\Models\Balance;
use PDO;
use PDOException;

class MySQLProcessorBalance implements BalanceInterface {
    private PDO $pdo;

    public function __construct() {
        // $this->pdo = $this->createPDOInstance();
        // $this->createTableIfNotExists();
        $this->pdo = DatabaseConnection::getInstance()->getConnection();
        $this->createTableIfNotExists();
    }

    // private function createPDOInstance(): PDO {
    //     $dsn = 'mysql:host=localhost;dbname=bangubank;charset=utf8mb4';
    //     $username = 'admin';
    //     $password = 'admin1234';

    //     try {
    //         return new PDO($dsn, $username, $password);
    //     } catch (PDOException $e) {
    //         echo phpinfo();
    //         echo "Error connecting to database: " . $e->getMessage();
    //         exit;
    //     }
    // }

    private function createTableIfNotExists(): void {
        $sql = "
            CREATE TABLE IF NOT EXISTS balances (
                userEmail VARCHAR(255) PRIMARY KEY,
                balance DECIMAL(10, 2) NOT NULL
            )
        ";

        try {
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            // Handle the exception if needed, for example, log the error
            echo "Error creating table: " . $e->getMessage();
        }
    }

    public function findBalanceByEmail(string $email): ?Balance {
        $sql = "SELECT * FROM balances WHERE userEmail = :email";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return new Balance($result['userEmail'], $result['balance']);
        }
        return null;
    }

    public function saveBalance(Balance $balance): bool {
        $sql = "INSERT INTO balances (userEmail, balance) VALUES (:email, :balance)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':email', $balance->getUserEmail());
        $stmt->bindParam(':balance', $balance->getBalance());

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function withdrawBalance(string $email, float $amount): bool {
        $userBalance = $this->findBalanceByEmail($email);
        if ($userBalance && $amount > 0) {
            $userBalance->updateBalance($amount * -1);
            return $this->update($userBalance);
        }
        return false;
    }

    public function depositBalance(string $email, float $amount): bool {
        $userBalance = $this->findBalanceByEmail($email);
        if ($userBalance && $amount > 0) {
            $userBalance->updateBalance($amount);
            return $this->update($userBalance);
        }
        return false;
    }

    private function update(Balance $balance): bool {
        $sql = "UPDATE balances SET balance = :balance WHERE userEmail = :email";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':balance', $balance->getBalance());
        $stmt->bindParam(':email', $balance->getUserEmail());

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
