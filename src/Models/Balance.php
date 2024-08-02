<?php

namespace App\Models;

class Balance {
    private $userEmail;
    private $balance;

    public function __construct($userEmail, $balance = 0) {
        $this->userEmail = $userEmail;
        $this->balance = $balance;
    }

    public function getUserEmail() {
        return $this->userEmail;
    }

    public function getBalance() {
        return $this->balance;
    }

    public function updateBalance($amount) {
        $this->balance += $amount;
    }
}
