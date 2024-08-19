<?php
namespace App\Databases\FileProcessor;

use App\Databases\Interfaces\BalanceInterface;
use App\Models\Balance;

class FileProcessorBalance implements BalanceInterface {
    const BALANCE_FILE_PATH = "../storage/balance.json";
    
    private string $filePath;
    public function __construct() {
        $this->filePath = self::BALANCE_FILE_PATH;
    }

    public function findBalanceByEmail(string $email):?Balance {
        $balances = $this->readBalanceFromFile();
        $userBalance = $this->array_find($balances, function ($balance) use ($email) {
            return $balance['userEmail'] === $email;
        });
        if($userBalance){
            return new Balance($userBalance['userEmail'],$userBalance['balance']);
        }else{
            return null;
        }
    }

    public function saveBalance(Balance $balance):bool {
        $balances = $this->readBalanceFromFile();
        $balances[] = [
            'userEmail' => $balance->getUserEmail(),
            'balance' => $balance->getBalance()
        ];
        return $this->writeBalanceToFile($balances);
    }

    public function withdrawBalance(string $email, float $amount):bool{
        $userBalance = $this->findBalanceByEmail($email);
        if($userBalance && $amount>0){
            $userBalance->updateBalance($amount*-1);
            return $this->update($userBalance);
        }
        return false;
    }

    public function depositBalance(string $email, float $amount):bool {
        $userBalance = $this->findBalanceByEmail($email);
        if($userBalance && $amount>0){
            $userBalance->updateBalance($amount);
            return $this->update($userBalance);
        }
        return false;
    }

    private function readBalanceFromFile():array {
        if (!file_exists($this->filePath)) {
            return [];
        }
        $data = file_get_contents($this->filePath);
        if(!$data){
            return [];
        }
        return json_decode($data, true) ?: [];
    }

    private function writeBalanceToFile($data):bool {
        if(file_put_contents($this->filePath, json_encode($data))){
            return true;
        }
        return false;
    }

    private function array_find(array $array, callable $callback) {
        foreach ($array as $element) {
            if ($callback($element)) {
                return $element;
            }
        }
        return null;
    }

    private function update(Balance $balance): bool {
        $balances = $this->readBalanceFromFile();
        $updated = false;
        
        foreach ($balances as &$existingBalance) {
            if ($existingBalance['userEmail'] === $balance->getUserEmail()) {
                $existingBalance['balance'] = $balance->getBalance();
                $updated = true;
                break;
            }
        }
        
        if ($updated) {
            return $this->writeBalanceToFile($balances);
        }
        return false;
    }
}
