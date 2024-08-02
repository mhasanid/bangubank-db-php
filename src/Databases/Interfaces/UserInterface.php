<?php

namespace App\Databases\Interfaces;
use App\Models\User;

interface UserInterface {
    public function getUsers():array;
    public function getUsersByRole($role):array;
    public function findUserById($id):?User;
    public function findUserByEmail($email):?User;
    public function saveUser(User $user):bool;
    public function isUserExist(User $user):bool;
}