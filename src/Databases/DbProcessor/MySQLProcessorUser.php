<?php
namespace App\Databases\DbProcessor;

use App\Databases\Interfaces\UserInterface;
use App\Models\User;
use PDO;

class MySQLProcessorUser implements UserInterface {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = DatabaseConnection::getInstance()->getConnection();
        $this->createTableIfNotExists();
    }


    public function getUsers(): array {
        $stmt = $this->pdo->query("SELECT * FROM users");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map([$this, 'mapToUser'], $results);
    }

    public function getUsersByRole($role): array {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE role = :role");
        $stmt->execute(['role' => $role]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map([$this, 'mapToUser'], $results);
    }

    public function findUserById($id): ?User {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        return $userData ? $this->mapToUser($userData) : null;
    }

    public function findUserByEmail($email): ?User {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        return $userData ? $this->mapToUser($userData) : null;
    }

    public function saveUser(User $user): bool {
        if ($this->isUserExist($user)) {
            return false;
        }
        $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)");
        return $stmt->execute([
            'name' => $user->name,
            'email' => $user->email,
            'password' => $user->password,
            'role' => $user->role,
        ]);
    }

    public function isUserExist(User $user): bool {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute(['email' => $user->email]);
        return $stmt->fetchColumn() > 0;
    }

    private function createTableIfNotExists(): void {
        $sql = "
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                role VARCHAR(50) NOT NULL
            )
        ";
        $this->pdo->exec($sql);
    }

    private function mapToUser(array $userData): User {
        $user = new User($userData['name'], $userData['email'], $userData['password'], $userData['role']);
        $user->setId($userData['id']);
        return $user;
    }
}
