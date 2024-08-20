<?php
require_once __DIR__ . '/src/Databases/Interfaces/UserInterface.php';
require_once __DIR__ . '/src/Databases/DbProcessor/DatabaseConnection.php';
require_once __DIR__ . '/src/Databases/DbProcessor/MySQLProcessorUser.php';
require_once __DIR__ . '/src/Databases/FileProcessor/FileProcessorUser.php';
require_once __DIR__ . '/src/Databases/UserStorage.php';
require_once __DIR__ . '/src/Models/User.php';
require_once __DIR__ . '/src/Utils/Utility.php';

use App\Databases\DbProcessor\MySQLProcessorUser;
use App\Databases\FileProcessor\FileProcessorUser;
use App\Databases\UserStorage;
use App\Models\User;
use App\Utils\Utility;

$config = require __DIR__ .'/config/config.php';

echo "\n".'BanguBank Admin Registration CLI.' . "\n\n";

$option = -1;

while ($option !== 0) {
    

    echo '1. Create Admin Account'. "\n";
    echo '2. Exit' . "\n\n";
    $option = (int) readline('Enter an option: ');

    echo "\n";
    
    switch ($option) {
        case 1:
            echo 'Please, insert the following info for Admin User registration:' . "\n";
            $name = (string) htmlspecialchars(trim(readline('Admin name: ')));
            $email = (string) htmlspecialchars(trim(readline('Admin email: ')));
            $password = (string) htmlspecialchars(trim(readline('Admin password: ')));
            $confirmPassword = (string) htmlspecialchars(trim(readline('Confirm password: ')));

            $adminUser = validAdminUser($name, $email, $password, $confirmPassword);
            if($adminUser){
                $userHelper = adminUserCreator($config);
                if($userHelper->isUserExist($adminUser)){
                    echo "\n" . 'User already exist. Try Again' . "\n\n";
                    continue 2;
                }
                if($userHelper->save($adminUser)){
                    echo "\nAdmin User created Successfully.\n";
                    break 2;
                }

            }

            break;
        case 2:
            echo "App Closed.\n";
            break 2;
        default:
            echo "Invalid option selected. Please try again.\n";
            continue 2;
    }

}

function validAdminUser(string $name, string $email, string $password, string $confirmPassword): ?User
{
    $errors = [];

    if (strlen($name) < 3) {
        $errors[] = 'Name must be at least 3 chars long.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email is not valid.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 chars long.';
    }
    if (!count($errors)) {
        if ($password !== $confirmPassword) {
            $errors[] = 'Confirm password did not matched.';
        }
    }

    if (count($errors)) {
        echo "\n\nPlease provide valid info!\n";
        foreach ($errors as $error) {
            echo 'Error: ' . $error . "\n";
        }
        echo "\n";
        return null;
    }
    $hashedPassword = password_hash(Utility::sanitize($password), PASSWORD_DEFAULT);
    $user = new User($name, $email, $hashedPassword, User::ADMIN_USER);
    return $user;
}

function adminUserCreator(array $config):UserStorage
{
    $storage = $config['storage'];

    if($storage==='database'){
        $userHelper = new UserStorage(new MySQLProcessorUser());
    }else if($storage==='file'){
        $userHelper = new UserStorage(new FileProcessorUser());
    }
    
    return $userHelper; 
}
