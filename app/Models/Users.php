<?php

namespace App\Models;

use CodeIgniter\Model;

class Users extends Model
{
    //constructor fucntion
    public function __construct() {
        // Initialize database connection and assign the query builder to the users table
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('users');
    }

    //login function
    public function login($email, $password, &$data = []) {
        $userResult = $this->builder->where('email', $email)->get();

        // Check if user exists
        if ($userResult->getNumRows() > 0) {
            // Fetch the user row as an associative array
            $data = $userResult->getRowArray();
            
            // Verify the password
            if (password_verify($password, $data['password'])) {
                return true; // Password is correct
            } else {
                return false; // Password is incorrect
            }
        }

        // User does not exist
        return false;
    }

    //get all users
    public function getAll(&$data = []) {
        $userResult = $this->builder
                                ->select('users.id, users.user_role_id, roles.name AS role_name, users.first_name, users.last_name, users.email, users.phone_number, users.street_name, users.house_number, users.city, users.zipcode, users.subscription, users.created_at')
                                ->join('roles', 'users.user_role_id = roles.id')
                                ->get();
        //check if there are results and return them
        if ($userResult->getNumRows() > 0) {
            $data = $userResult->getResultArray();
            return true;
        } else {
            return false;
        }
    }

    //delete user by id
    public function DeleteById($user_id) {
        // Check if the user exists
        $userResult = $this->builder()
                           ->where('id', $user_id)
                           ->get();
    
        if ($userResult->getNumRows() > 0) {
            // User exists, proceed to delete
            $this->builder()
                 ->where('id', $user_id)
                 ->delete();
    
            return true; // Successfully deleted
        } else {
            return false; // User not found
        }
    }

    //register function
    public function createUser($createNewUser) {
        // Attempt to insert the new user data into the 'users' table
        $insertUser = $this->builder->insert($createNewUser);
        
        // Check if the insertion was successful
        if ($insertUser) {
            return true; // Successfully inserted
        } else {
            return false; // Insertion failed
        }
    }

    //check if given email exists in database (for when creating an account (register))
    public function checkIfEmailExists($arrCheckEmail) {
        $userResult = $this->builder->where('email', $arrCheckEmail)->get();

        // Check if user exists
        if ($userResult->getNumRows() > 0) {
            return true; //user already exists
        } else {
            return false; //user doesnt exist yet
        }
    }
}