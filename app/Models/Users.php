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
}