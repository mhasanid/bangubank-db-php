<?php
namespace App\Databases\DbProcessor;

use App\Databases\Interfaces\TransactionInterface;
use App\Models\Transaction;
use DateTime;
use PDO;

class MySQLProcessorTransaction implements TransactionInterface {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = DatabaseConnection::getInstance()->getConnection();
        $this->createTableIfNotExists();
    }

    private function createTableIfNotExists(): void {
        $sql = "CREATE TABLE IF NOT EXISTS transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            userEmail VARCHAR(255) NOT NULL,
            othersEmail VARCHAR(255) NOT NULL,
            type VARCHAR(50) NOT NULL,
            amount DECIMAL(10, 2) NOT NULL,
            dateTime DATETIME NOT NULL
        )";

        $this->pdo->exec($sql);
    }

    public function getTransactions(): array {
        $sql = 'SELECT * FROM transactions';
        $stmt = $this->pdo->query($sql);
        $allTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($allTransactions as &$transaction) {
            if (isset($transaction['dateTime'])) {
                $datetime = new DateTime($transaction['dateTime']);
                $transaction['dateTime'] = [
                    'date' => $datetime->format('Y-m-d H:i:s'),
                    'timezone_type' => $datetime->getTimezone()->getLocation()['timezone_id'] ?? null,
                    'timezone' => $datetime->getTimezone()->getName()
                ];
            }
        }
        return $allTransactions;
    }

    public function getTransactionByEmail(string $email): array {
        $sql = 'SELECT * FROM transactions WHERE userEmail = :email';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        $userTransaction = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($userTransaction as &$transaction) {
            if (isset($transaction['dateTime'])) {
                $datetime = new DateTime($transaction['dateTime']);
                $transaction['dateTime'] = [
                    'date' => $datetime->format('Y-m-d H:i:s'),
                    'timezone_type' => $datetime->getTimezone()->getLocation()['timezone_id'] ?? null,
                    'timezone' => $datetime->getTimezone()->getName()
                ];
            }
        }
        return $userTransaction;
    }

    public function saveTransaction(Transaction $transaction): bool {
        $sql = 'INSERT INTO transactions (userEmail, othersEmail, type, amount, dateTime) 
                VALUES (:userEmail, :othersEmail, :type, :amount, :dateTime)';
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'userEmail' => $transaction->userEmail,
            'othersEmail' => $transaction->othersEmail,
            'type' => $transaction->type,
            'amount' => $transaction->amount,
            'dateTime' => $transaction->dateTime->format('Y-m-d H:i:s')
        ]);
    }
}
