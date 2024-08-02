<?php
namespace App\Databases;
use App\Databases\DatabaseInterface;
use App\Models\Transaction;

class TransactionStorage {
    private $storage;

    public function __construct(DatabaseInterface $storage) {
        $this->storage = $storage;
    }

    public function all() {
        return $this->storage->read();
    }

    public function save(Transaction $transaction):bool {
        $transactions = $this->all();
        $transactions[] = [
            'userEmail' => $transaction->userEmail,
            'othersEmail' => $transaction->othersEmail,
            'type' => $transaction->type,
            'amount' => $transaction->amount,
            'dateTime'=>$transaction->dateTime
        ];
        return $this->storage->write($transactions);
    }

    public function findByEmail($email) {
        $transactions = $this->all();
        return array_filter($transactions, function ($transaction) use ($email) {
            return $transaction['userEmail'] === $email;
        });
    }
}
