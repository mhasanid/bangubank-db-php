<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Databases\BalanceStorage;
use App\Databases\JsonFileProcessor;
use App\Models\User;
use App\Databases\UserStorage;
use App\Models\Balance;
use App\Utils\Utility;

class AuthController extends Controller {
    private UserStorage $userHelper;
    private BalanceStorage $balanceHelper;

    public function __construct()
    {
        $this->userHelper = new UserStorage(new JsonFileProcessor(JsonFileProcessor::USER_FILE_PATH));
        $this->balanceHelper = new BalanceStorage(new JsonFileProcessor(JsonFileProcessor::BALANCE_FILE_PATH));
    }

    public function showHomepage() {
        if(!$this->showLoggedinUserDashboard()){
            $this->view('landing/homepage');
        }
    }

    public function showLogin() {
        $error = Utility::flash('error');
        if(!$this->showLoggedinUserDashboard()){
            $this->view('auth/login',['error'=>$error]);
        }
    }

    public function login() {
        $email = Utility::sanitize($_POST['email']);
        $password = Utility::sanitize($_POST['password']);

        $user = $this->userHelper->findByEmail($email);
        
        if (!$user) {
            Utility::flash('error','You are not registered. Please register.');
            $this->redirect('login');
        }
        if(!$user->verifyPassword($password)){
            Utility::flash('error','Please try with correct credentials.');
            $this->redirect('login');
        }
        $_SESSION['user'] = $user->email;              
        $this->showLoggedinUserDashboard();
    }

    public function showRegister() {
        $error = Utility::flash('error');
        if(!$this->showLoggedinUserDashboard()){
            $this->view('auth/register', ['error'=>$error]);
        }
    }

    public function register() {
        $name = Utility::sanitize($_POST['name']);
        $email = Utility::sanitize($_POST['email']);
        
        if(strlen(Utility::sanitize($_POST['password']))<8){
            Utility::flash('error','Password must be at least 8 character.');
            $this->redirect('register');
        }

        $password = password_hash(Utility::sanitize($_POST['password']), PASSWORD_DEFAULT);;
        
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            Utility::flash('error','Please insert a valid email address.');
            $this->redirect('register');
        }

        if(!$name){
            Utility::flash('error','Customer name required.');
            $this->redirect('register');
        }

        $user = new User($name, $email, $password);

        if($this->userHelper->isUserExist($user)){
            Utility::flash('error','You are already registered. Please login.');
            $this->redirect('login');
        }
        if(!$this->userHelper->save($user)){
            Utility::flash('error','Error occured! User cannot be registered.');
            $this->redirect('register');
        }
        $_SESSION['user'] = $user->email;
        $this->showLoggedinUserDashboard();
    }

    public function logout() {
        session_destroy();
        $this->redirect('login');

    }

    private function showLoggedinUserDashboard():bool{
        $user_email = $_SESSION['user'] ?: null;
        if(!$user_email){
            return false;
        }
        $user = $this->userHelper->findByEmail($user_email);
        if(!$user){
            return false;
        }
        if($user->role===User::CUSTOMER_USER){
            $userBalance = $this->balanceHelper->getBalanceByEmail($user->email);
            if(!$userBalance){
                $this->balanceHelper->save(new Balance($user->email));
            }
            $this->redirect('customer/transactions');
            return true;
        }elseif ($user->role===User::ADMIN_USER){
            $this->redirect('admin/customers');
            return true;
        }
    }
}
