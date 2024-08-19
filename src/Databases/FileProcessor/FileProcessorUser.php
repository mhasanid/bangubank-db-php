<?php
namespace App\Databases\FileProcessor;

use App\Databases\Interfaces\UserInterface;
use App\Models\User;

class FileProcessorUser implements UserInterface {
    const USER_FILE_PATH = "../storage/users.json";

    private string $filePath;

    public function __construct() {
        $this->filePath = self::USER_FILE_PATH;
    }

    public function getUsers(): array {
        return $this->readDataFromFile();
    }

    public function getUsersByRole($role): array {
        $users = $this->readDataFromFile();
        $usersByRole = [];
        
        foreach ($users as $userData) {
            if ($userData['role'] === $role) {
                $user = new User($userData['name'], $userData['email'], $userData['password'], $userData['role']);
                $user->setId($userData['id']);
                $usersByRole[] = $user;
            }
        }
        return $usersByRole;
    }

    public function findUserById($id): ?User {
        $users = $this->readDataFromFile();
        foreach ($users as $userData) {
            if ($userData['id'] == $id) {
                $user = new User($userData['name'], $userData['email'], $userData['password'], $userData['role']);
                $user->setId($userData['id']);
                return $user;
            }
        }
        return null;
    }

    public function findUserByEmail($email): ?User {
        $users = $this->readDataFromFile();
        foreach ($users as $userData) {
            if ($userData['email'] === $email) {
                $user = new User($userData['name'], $userData['email'], $userData['password'], $userData['role']);
                $user->setId($userData['id']);
                return $user;
            }
        }
        return null;
    }

    public function saveUser(User $user): bool {
        $users = $this->readDataFromFile();
        if($this->isUserExist($user)){
            return false;
        }
        $user->setId($this->setIdIncrement($users));
        $users[] = $user->toArray();
        return $this->writeDataToFile($users);
    }

    public function isUserExist(User $user): bool{
        $users = $this->readDataFromFile();

        foreach ($users as $userData) {
            if ($userData['email'] === $user->email) {
                return true;
            }
        }
        return false;
    }

    private function readDataFromFile():array {
        if (!file_exists($this->filePath)) {
            return [];
        }
        $data = file_get_contents($this->filePath);
        if(!$data){
            return [];
        }
        return json_decode($data, true) ?: [];
    }

    private function writeDataToFile($data):bool {
        if(file_put_contents($this->filePath, json_encode($data))){
            return true;
        }
        return false;
    }

    private function setIdIncrement(array $users):int{
        if(empty($users)){
            return 1;
        }
        $lastId = (int)$users[count($users)-1]['id'];
        return $lastId+1;
        
    }
}
