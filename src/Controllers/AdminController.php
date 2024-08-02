<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Databases\BalanceStorage;
use App\Databases\JsonFileProcessor;
use App\Databases\TransactionStorage;
use App\Databases\UserStorage;
use App\Models\Balance;
use App\Models\User;
use App\Utils\Utility;

class AdminController extends Controller {
    private UserStorage $userHelper;
    private TransactionStorage $transactionHelper;
    private BalanceStorage $balanceHelper;

    public function __construct()
    {
        $this->userHelper = new UserStorage(new JsonFileProcessor(JsonFileProcessor::USER_FILE_PATH));
        $this->transactionHelper = new TransactionStorage(new JsonFileProcessor(JsonFileProcessor::TRANSACTION_FILE_PATH));
        $this->balanceHelper = new BalanceStorage(new JsonFileProcessor(JsonFileProcessor::BALANCE_FILE_PATH));
    }

    public function showTransactions() {
        $transactions = [];
        $email = $_SESSION['user'];
        $loggedinUser = $this->userHelper->findByEmail($email);
        $allTransactions = $this->transactionHelper->all();
        foreach($allTransactions as $transaction){
            $userName = $this->userHelper->findByEmail($transaction['othersEmail'])->name;
            $transaction['receiver-name']= $userName;
            $transactions[]=$transaction;
        }
        if(!$loggedinUser){
            $this->redirect('login');
        }
        $this->view('admin/transactions', ['loggedinUser'=>$loggedinUser,'transactions' => $transactions]);
    }

    public function showUserTransactions() {
        $transactions = [];
        $url = $_SERVER['REQUEST_URI'];
        $id = end(explode('/', $url));
        $loggedinUser = $this->userHelper->findByEmail($_SESSION['user']);
        $customerUser = $this->userHelper->findById($id);
        $userTransactions = $this->transactionHelper->findByEmail($customerUser->email);
        foreach($userTransactions as $transaction){
            $userName = $this->userHelper->findByEmail($transaction['othersEmail'])->name;
            $transaction['receiver-name']= $userName;
            $transactions[]=$transaction;
        }
        if(!$loggedinUser){
            $this->redirect('login');
        }
        $this->view('admin/customerTransactions', ['loggedinUser'=>$loggedinUser, 'customerUser'=>$customerUser, 'transactions' => $transactions]);
    }

    public function showCustomers() {
        $email = $_SESSION['user'];
        $loggedinUser = $this->userHelper->findByEmail($email);
        $customers = $this->userHelper->findAllByRole('customer');
        if(!$loggedinUser){
            $this->redirect('login');
        }
        $this->view('admin/customers', ['loggedinUser'=>$loggedinUser,'customers' => $customers]);
    }

    public function showAddCustomer() {
        $email = $_SESSION['user'];
        $loggedinUser = $this->userHelper->findByEmail($email);
        $error = Utility::flash('error');

        if(!$loggedinUser){
            $this->redirect('login');
        }
        $this->view('admin/addCustomer', ['loggedinUser'=>$loggedinUser,'error'=>$error]);
    }

    public function addCustomer() {
        $firstName = Utility::sanitize($_POST['first-name']);
        $lastName = Utility::sanitize($_POST['last-name']);
        $email = Utility::sanitize($_POST['email']);

        if(!$firstName || !$lastName){
            Utility::flash('error','First Name and Last Name required.');
            $this->redirect('admin/add-customer');
        }
        if(strlen(Utility::sanitize($_POST['password']))<8){
            Utility::flash('error','Password must be at least 8 character.');
            $this->redirect('admin/add-customer');
        }
        $password = password_hash(Utility::sanitize($_POST['password']), PASSWORD_DEFAULT);;
        
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            Utility::flash('error','Please insert a valid email address.');
            $this->redirect('admin/add-customer');
        }
        $name = $firstName .' '. $lastName;

        $user = new User($name, $email, $password);

        if($this->userHelper->isUserExist($user)){
            Utility::flash('error','You are already registered. Please login.');
            $this->redirect('admin/add-customer');
        }
        if(!$this->userHelper->save($user)){
            Utility::flash('error','Error occured! User cannot be added.');
            $this->redirect('admin/add-customer');
        }
        Utility::dd(Utility::flash('error'));
        $userBalance = $this->balanceHelper->getBalanceByEmail($user->email);
        if(!$userBalance){
            $this->balanceHelper->save(new Balance($user->email));
        }
        $this->redirect('admin/customers');
        
    }


}
