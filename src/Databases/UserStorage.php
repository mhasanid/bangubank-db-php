<?php

namespace App\Databases;

use App\Databases\Interfaces\UserInterface;
use App\Models\User;

class UserStorage {

    private $storage;

    public function __construct(UserInterface $storage) {
        $this->storage = $storage;
    }

    public function all(): array {
        return $this->storage->getUsers();
    }

    public function findAllByRole($role): array {
        return $this->storage->getUsersByRole($role);
    }

    public function findById($id): ?User {
        return $this->storage->findUserById($id);
    }

    public function findByEmail($email): ?User {
        return $this->storage->findUserByEmail($email);
    }  

    public function save(User $user): bool {
        return $this->storage->saveUser($user);
    }

    public function isUserExist(User $user):bool{
        return $this->storage->isUserExist($user);
    }
}
