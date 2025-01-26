<?php

namespace App\Models;

use CodeIgniter\Model;

class Messages extends Model
{
    //constructor fucntion
    public function __construct() {
        //initialize database connection and assign the query builder to the users table
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('messages');
    }

    //get all users
    public function getAll(&$data = []) {
        $messageResult = $this->builder
                                ->select('messages.id, messages.language, messages.name, messages.message')
                                ->get();
        //check if there are results and return them
        if ($messageResult->getNumRows() > 0) {
            $data = $messageResult->getResultArray();
            return true;
        } else {
            return false;
        }
    }

    //insert given message
    public function insertMessage($data) {
        // Check if a message with the same name and language exists
        $existingMessage = $this->builder
                                ->where('name', $data['name'])
                                ->where('language', $data['language'])
                                ->get();
    
        // If a match is found for both name and language, return false
        if ($existingMessage->getNumRows() > 0) {
            return false;
        }
    
        // Check if a message with the same name exists but with a different language
        $existingNameOnly = $this->builder
                                ->where('name', $data['name'])
                                ->get();
    
        // If the name exists but the language doesn't, allow the insertion
        if ($existingNameOnly->getNumRows() > 0) {
            // Proceed to insert the message since the language is different
            $this->builder->insert($data);
            return true; // Successfully inserted
        }
    
        // If neither the name nor the language exists, proceed to insert the new message
        $this->builder->insert($data);
    
        // Confirm that the message has been inserted
        $newMessage = $this->builder
                            ->where('name', $data['name'])
                            ->where('language', $data['language'])
                            ->get();
    
        // If the new message is retrieved successfully, return true
        if ($newMessage->getNumRows() > 0) {
            return true;
        }
    
        // Default return false in case of any issues
        return false;
    }

    //delete by id
    public function DeleteById($message_id) {
        //check if the user exists
        $messageResult = $this->builder()
                            ->where('id', $message_id)
                            ->get();
    
        if ($messageResult->getNumRows() > 0) {
            //user exists, proceed to delete
            $this->builder()
                    ->where('id', $message_id)
                    ->delete();
    
            return true; //successfully deleted
        } else {
            return false; //user not found
        }
    }
    
    public function getProductFieldTranslation(&$product) {
        $messageResult = $this->builder
                                ->select('messages.message')
                                ->where('name', $product)
                                ->get();
        //check if there are results and return them
        if ($messageResult->getNumRows() > 0) {
            $product = $messageResult->getResultArray();
            return true;
        } else {
            return false;
        }
    }
}