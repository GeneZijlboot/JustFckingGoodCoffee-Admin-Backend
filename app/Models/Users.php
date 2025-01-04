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
    public function login($email, $password, &$userData = []) {
        $userResult = $this->builder->where('email', $email)->get();

        // Check if user exists
        if ($userResult->getNumRows() > 0) {
            // Fetch the user row as an associative array
            $userData = $userResult->getRowArray();
            
            // Verify the password
            if (password_verify($password, $userData['password'])) {
                return true; // Password is correct
            } else {
                return false; // Password is incorrect
            }
        }

        // User does not exist
        return false;
    }

    //register function
    public function createUser($createNewUser) {
        // Connect to the database
        $db = \Config\Database::connect();
        
        // Get the table builder for the 'users' table
        $builder = $db->table('users');
        
        // Attempt to insert the new user data into the 'users' table
        $insertSuccess = $builder->insert($createNewUser);
        
        // Check if the insertion was successful
        if ($insertSuccess) {
            return true; // Successfully inserted
        } else {
            return false; // Insertion failed
        }
    }

    //check if given email exists in database (for when creating an account (register))
    public function checkIfEmailExists($arrCheckEmail) {
        $db = \Config\Database::connect();
        $builder = $db->table('users');
        $userResult = $builder->where('email', $arrCheckEmail)->get();

        // Check if user exists
        if ($userResult->getNumRows() > 0) {
            return true; //user already exists
        } else {
            return false; //user doesnt exist yet
        }
    }

    //function to create a token
    public function createToken($user_email, &$token = '') {
        $userResult = $this->builder->where('email', $user_email)->get();

        if ($userResult->getNumRows() > 0) {
            //creating token
            $token = bin2hex(random_bytes(32));

            // Update the token field for the user
            $this->builder->where('email', $user_email)->update(['token' => $token]);

            if ($this->db->affectedRows() > 0) {
                return true;
            } else {
                return false;
            }
        }
        // Return false if no user is found with the email
        return false;
    }

    //changepassword function
    public function changePassword($arrCheckEmail, $password, $token) {
        $userResult = $this->builder->where('email', $arrCheckEmail)->where('token', $token)->get();

        //email with its token found
        if ($userResult->getNumRows() > 0) {
            //insert new password 
            $this->builder->where('email', $arrCheckEmail)->where('token', $token)->update(['password' => $password]);

            if ($this->db->affectedRows() > 0) {
                //delete token
                $this->builder->where('email', $arrCheckEmail)->where('token', $token)->update(['token' => '']);
                //do a check also if token succesfully deleted
                return true; //succesfully updated password
            } else { 
                return false; //failed to insert new password
            }
        } else {
            return false; //email not found
        }
    }

    //check if a billing address exists in the currenuser data
    public function checkCurrentUsersBillingAddress($currentUser) {
        $db = \Config\Database::connect();
        $builder = $db->table('users');
        $userResult = $builder
                        ->where('id', $currentUser)
                        ->get();
    
        // Check if a user is found
        if ($userResult->getNumRows() > 0) {
            $userRow = $userResult->getRow();
    
            // Check if all billing-related fields are NULL
            if (
                is_null($userRow->phone_number) &&
                is_null($userRow->street_name) &&
                is_null($userRow->house_number) &&
                is_null($userRow->city) &&
                is_null($userRow->zipcode)
            ) {
                return false; //wanted billing info not filled it -> give back false
            } else {
                return true; //all billing info is filled out -> return true
            }
        } else {
            return false; //user not even found ( jsut a check )
        } 
    }

    public function insertBillingInformation(&$data, $currentUser) {
        $db = \Config\Database::connect();
        $builder = $db->table('users');
    
        // Check if the user exists
        $userResult = $builder
                        ->where('id', $currentUser)
                        ->get();
    
        if ($userResult->getNumRows() > 0) {
            // User exists, update the billing information
            $builder->where('id', $currentUser);
            $builder->update($data);
    
            return true; // Billing information updated successfully
        } else {
            return false; // User not found
        }
    }
}