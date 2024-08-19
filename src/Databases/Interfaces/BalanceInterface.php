<?php

namespace App\Databases\Interfaces;

use App\Models\Balance;

interface BalanceInterface {
    public function findBalanceByEmail(string $email):?Balance;
    public function saveBalance(Balance $balance):bool;
    public function withdrawBalance(string $email, float $amount):bool;
    public function depositBalance(string $email, float $amount):bool;

}