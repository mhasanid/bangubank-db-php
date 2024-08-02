<?php

namespace App\Models;

use DateTime;
use DateTimeZone;

class Transaction {
    public DateTime $dateTime;

    public function __construct(
        public string $userEmail,
        public string $othersEmail,
        public string $type,
        public float $amount,
    ) {
        $this->dateTime = new DateTime('now', new DateTimeZone('Asia/Dhaka'));
    }
}

