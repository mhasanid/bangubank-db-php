<?php
namespace App\Databases;
use App\Databases\Interfaces\TransactionInterface;
use App\Models\Transaction;

class TransactionStorage {
    private $storage;

    public function __construct(TransactionInterface $storage) {
        $this->storage = $storage;
    }

    public function all() {
        return $this->storage->getTransactions();
    }
    
    public function findByEmail($email):array {
       return $this->storage->getTransactionByEmail($email);
    }

    public function save(Transaction $transaction):bool {
        return $this->storage->saveTransaction($transaction);
    }
}
