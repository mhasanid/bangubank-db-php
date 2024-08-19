<?php

namespace App\Databases\Interfaces;

use App\Models\Transaction;

interface TransactionInterface {
    public function getTransactions():array;
    public function getTransactionByEmail(string $email):array;
    public function saveTransaction(Transaction $transaction):bool;
}