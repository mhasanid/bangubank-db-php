<?php
namespace App\Databases;
use App\Databases\Interfaces\BalanceInterface;
use App\Models\Balance;

class BalanceStorage {
    private $storage;

    public function __construct(BalanceInterface $storage) {
        $this->storage = $storage;
    }

    public function save(Balance $balance):bool{
        return $this->storage->saveBalance($balance);
    }

    public function getBalanceByEmail($email):?Balance {
        return $this->storage->findBalanceByEmail($email);
    }

    public function withdrawBalance($email, $amount):bool {
        return $this->storage->withdrawBalance($email, $amount);
    }

    public function depositBalance($email, $amount):bool {
        return $this->storage->depositBalance($email, $amount);
    }
}
