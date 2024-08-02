<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Databases\BalanceStorage;
use App\Databases\JsonFileProcessor;
use App\Databases\TransactionStorage;
use App\Databases\UserStorage;
use App\Models\Transaction;
use App\Models\User;
use App\Utils\Utility;

class CustomerController extends Controller {
    
    private TransactionStorage $transactionHelper;
    private BalanceStorage $balanceHelper;
    private UserStorage $userHelper;

    public function __construct()
    {
        $this->transactionHelper = new TransactionStorage(new JsonFileProcessor(JsonFileProcessor::TRANSACTION_FILE_PATH));
        $this->balanceHelper = new BalanceStorage(new JsonFileProcessor(JsonFileProcessor::BALANCE_FILE_PATH));
        $this->userHelper = new UserStorage(new JsonFileProcessor(JsonFileProcessor::USER_FILE_PATH));
    }

    public function transactions() {
        $transactions = [];
        $email = $_SESSION['user'];
        $userTransactions = $this->transactionHelper->findByEmail($email);
        $loggedinUser = $this->userHelper->findByEmail($email);
        foreach($userTransactions as $transaction){
            $userName = $this->userHelper->findByEmail($transaction['othersEmail'])->name;
            $transaction['name']= $userName;
            $transactions[]=$transaction;
        }
        $balance = $this->balanceHelper->getBalanceByEmail($email);
        if(!$loggedinUser){
            $this->redirect('login');
        }
        $this->view('customer/transactions', ['loggedinUser' => $loggedinUser,'transactions' => $transactions, 'balance'=>$balance]);
    }

    public function showDeposit(){
        $email = $_SESSION['user'];
        $loggedinUser = $this->userHelper->findByEmail($email);
        $balance = $this->balanceHelper->getBalanceByEmail($email);
        if(!$loggedinUser){
            $this->redirect('login');
        }
        $this->view('customer/deposit', ['loggedinUser' => $loggedinUser,'balance'=>$balance]);
    }

    public function deposit() {
        $email = $_SESSION['user'];
        $amount = (float)$_POST['amount'];
        if($amount<=0){
            Utility::flash('route','deposit');
            Utility::flash('operation-error','Diposit amount cannot be negative or zero.');
            $this->redirect('customer/operation-failed');
        }
        $transaction = new Transaction($email, $email, 'deposit', $amount);
        $this->transactionHelper->save($transaction);
        $this->balanceHelper->depositBalance($email, $amount);
        
        $this->redirect('customer/transactions');
 
    }


    public function showWithdraw(){
        $email = $_SESSION['user'];
        $loggedinUser = $this->userHelper->findByEmail($email);
        $balance = $this->balanceHelper->getBalanceByEmail($email);
        if(!$loggedinUser){
            $this->redirect('login');
        }
        $this->view('customer/withdraw', ['loggedinUser' => $loggedinUser,'balance'=>$balance]);
    }

    public function withdraw() {
        $email = $_SESSION['user'];
        $amount = (float)$_POST['amount'];
        $balance = (float)$this->balanceHelper->getBalanceByEmail($email)->getBalance();
        if($amount<=0){
            Utility::flash('route','withdraw');
            Utility::flash('operation-error','Withdrawal amount cannot be negative or zero.');
            $this->redirect('customer/operation-failed');
        }
        if($balance<$amount){
            Utility::flash('route','withdraw');
            Utility::flash('operation-error','Insufficient Balance!');
            $this->redirect('customer/operation-failed');
        }
        $transaction = new Transaction($email, $email, 'withdraw', $amount);
        $this->transactionHelper->save($transaction);
        $this->balanceHelper->withdrawBalance($email, $amount);
        
        $this->redirect('customer/transactions');

    }

    public function showTransfer(){
        $email = $_SESSION['user'];
        $loggedinUser = $this->userHelper->findByEmail($email);
        $balance = $this->balanceHelper->getBalanceByEmail($email);
        if(!$loggedinUser){
            $this->redirect('login');
        }
        $this->view('customer/transfer', ['loggedinUser' => $loggedinUser,'balance'=>$balance]);
    }

    public function operationFailed(){
        $email = $_SESSION['user'];
        $loggedinUser = $this->userHelper->findByEmail($email);
        $balance = $this->balanceHelper->getBalanceByEmail($email);
        $errorMessage = Utility::flash('operation-error');
        $route = Utility::flash('route');
        if(!$loggedinUser){
            $this->redirect('login');
        }
        $this->view('pages/operationFailed', ['loggedinUser' => $loggedinUser,'balance'=>$balance, 'errorMessage'=>$errorMessage, 'route'=>$route]);
    }

    public function transfer() {
        $email = $_SESSION['user'];
        $recipientEmail = $_POST['email'];
        $amount = (float)$_POST['amount'];
        $recipientUser = $this->userHelper->findByEmail($recipientEmail);
        $balance = (float)$this->balanceHelper->getBalanceByEmail($email)->getBalance();

        if(!$recipientUser){
            Utility::flash('route','transfer');
            Utility::flash('operation-error','No recipient found!');
            $this->redirect('customer/operation-failed');
        }
        if($recipientUser->email===$email){
            Utility::flash('route','transfer');
            Utility::flash('operation-error','Please enter the recipient email not yours.');
            $this->redirect('customer/operation-failed');
        }
        if($recipientUser->role==='admin'){
            Utility::flash('route','transfer');
            Utility::flash('operation-error','Please enter a customer email.');
            $this->redirect('customer/operation-failed');
        }
        if($amount<=0){
            Utility::flash('route','transfer');
            Utility::flash('operation-error','Withdrawal amount cannot be negative or zero.');
            $this->redirect('customer/operation-failed');
        }
        if($balance<$amount){
            Utility::flash('route','transfer');
            Utility::flash('operation-error','Insufficient Balance!');
            $this->redirect('customer/operation-failed');
        }
        $transaction = new Transaction($email, $recipientEmail, 'withdraw', $amount);
        $this->transactionHelper->save($transaction);
        $this->balanceHelper->withdrawBalance($email, $amount);

        $transaction = new Transaction($recipientEmail, $email, 'deposit', $amount);
        $this->transactionHelper->save($transaction);
        $this->balanceHelper->depositBalance($recipientEmail, $amount);

        $this->redirect('customer/transactions');
        

    }

}
