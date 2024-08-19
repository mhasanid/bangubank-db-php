<?php
namespace App\Databases\FileProcessor;

use App\Databases\Interfaces\TransactionInterface;
use App\Models\Transaction;
use App\Utils\Utility;

class FileProcessorTransaction implements TransactionInterface {
    const TRANSACTION_FILE_PATH = "../storage/transaction.json";

    private string $filePath;
    public function __construct() {
        $this->filePath = self::TRANSACTION_FILE_PATH;
    }

    public function getTransactions(): array{
        return $this->readDataFromFile();
    }

    public function getTransactionByEmail(string $email): array{
        $transactions = $this->readDataFromFile();
        Utility::dd($transactions);
        return array_filter($transactions, function ($transaction) use ($email) {
            return $transaction['userEmail'] === $email;
        });
    }

    public function saveTransaction(Transaction $transaction):bool{
        $transactions = $this->readDataFromFile();
        $transactions[] = [
            'userEmail' => $transaction->userEmail,
            'othersEmail' => $transaction->othersEmail,
            'type' => $transaction->type,
            'amount' => $transaction->amount,
            'dateTime'=>$transaction->dateTime
        ];
        return $this->writeDataToFile($transactions);
    }


    public function readDataFromFile() {
        if (!file_exists($this->filePath)) {
            return [];
        }
        $data = file_get_contents($this->filePath);
        if(!$data){
            return [];
        }
        return json_decode($data, true) ?: [];
    }

    public function writeDataToFile($data):bool {
        if(file_put_contents($this->filePath, json_encode($data))){
            return true;
        }
        return false;
    }
}
