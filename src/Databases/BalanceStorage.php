<?php
namespace App\Databases;
use App\Databases\DatabaseInterface;
use App\Models\Balance;

class BalanceStorage {
    private $storage;

    public function __construct(DatabaseInterface $storage) {
        $this->storage = $storage;
    }

    public function all() {
        return $this->storage->read();
    }

    public function save(Balance $balance):bool{
        $balances = $this->all();
        $balances[] = [
            'userEmail' => $balance->getUserEmail(),
            'balance' => $balance->getBalance()
        ];
        return $this->storage->write($balances);
    }

    public function getBalanceByEmail($email):?Balance {
        $balances = $this->all();
        $userBalance = $this->array_find($balances, function ($balance) use ($email) {
            return $balance['userEmail'] === $email;
        });
        if($userBalance){
            return new Balance($userBalance['userEmail'],$userBalance['balance']);
        }else{
            return null;
        }
    }

    public function withdrawBalance($email, $amount):bool {
        $userBalance = $this->getBalanceByEmail($email);
        if($userBalance && $amount>0){
            $userBalance->updateBalance($amount*-1);
            return $this->update($userBalance);
        }
        return false;
    }

    public function depositBalance($email, $amount):bool {
        $userBalance = $this->getBalanceByEmail($email);
        if($userBalance && $amount>0){
            $userBalance->updateBalance($amount);
            return $this->update($userBalance);
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
        $balances = $this->all();
        
        $updated = false;
        
        foreach ($balances as &$existingBalance) {
            if ($existingBalance['userEmail'] === $balance->getUserEmail()) {
                $existingBalance['balance'] = $balance->getBalance();
                $updated = true;
                break;
            }
        }
        
        if ($updated) {
            return $this->storage->write($balances);
        }
        return false;
    }
}
