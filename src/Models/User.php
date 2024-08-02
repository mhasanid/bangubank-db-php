<?php

namespace App\Models;

class User {
    const ADMIN_USER = "admin";
    const CUSTOMER_USER = "customer";
    public int $id;
    
    public function __construct(
        public string $name,
        public string $email,
        private string $password,
        public string $role = self::CUSTOMER_USER
    ) {

    }

    public function setId(int $id){
        $this->id = $id;
    }

    
    public function verifyPassword(string $password) {
        return password_verify($password, $this->password);
    }

    public function toArray(): array {
        return [
            'id'=> $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'role' => $this->role
        ];
    }
}
