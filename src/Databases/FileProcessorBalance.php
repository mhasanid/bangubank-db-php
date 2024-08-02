<?php
namespace App\Databases;

class JsonFileProcessor implements DatabaseInterface {
    const TRANSACTION_FILE_PATH = "../storage/transaction.json";
    const BALANCE_FILE_PATH = "../storage/balance.json";
    const USER_FILE_PATH = "../storage/users.json";

    public function __construct(
        private string $filePath
        ) {
    }

    public function read() {
        if (!file_exists($this->filePath)) {
            return [];
        }
        $data = file_get_contents($this->filePath);
        if(!$data){
            return [];
        }
        return json_decode($data, true) ?: [];
    }

    public function write($data):bool {
        if(file_put_contents($this->filePath, json_encode($data))){
            return true;
        }
        return false;
    }
}
