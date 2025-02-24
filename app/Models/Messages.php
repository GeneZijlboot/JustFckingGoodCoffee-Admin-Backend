<?php

namespace App\Models;

use CodeIgniter\Model;

class Messages extends Model
{
    //constructor function
    public function __construct() {
        //initialize database connection and assign the query builder to the users table
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('messages');
    }

    //get all messages
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

    //get all rows based on the given searchValue
    public function getBySearchParam(&$searchValue) {
        if (!empty($searchValue)) {
            $this->builder->groupStart()
                    ->orLike('messages.id', $searchValue)
                    ->orLike('messages.language', $searchValue)
                    ->orLike('messages.name', $searchValue)
                    ->orLike('messages.message', $searchValue)
                    ->groupEnd();
        }
        
        $messageResult = $this->builder->get();

        if ($messageResult->getNumRows() > 0) {
            $searchValue = $messageResult->getResultArray();
            foreach ($searchValue as &$translations) { // Use a reference to modify the actual array element
                $translations['controller'] = 'Message';
            }
            return true;
        } else {
            return false;
        }
    }

    //get all getWithLanguage messages
    public function getWithLanguage(&$data = []) {
        $messageResult = $this->builder
                                ->select('messages.language, messages.name, messages.message')
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

    //update a message
    public function updateMessage($data) {
        $newMessage = $this->builder
                                ->where('id', $data['id'])
                                ->get();

        //check if the message exists
        if ($newMessage->getNumRows() == 1) {
        //update the message
            $this->builder
                    ->where('id', $data['id'])
                    ->update(['name' => $data['name'], 'language' => $data['language'], 'message' => $data['message']]);
            
            $updatedMessage = $this->builder
                    ->where(['id' => $data['id'], 'name' => $data['name'], 'language' => $data['language'], 'message' => $data['message']])
                    ->get();
                    
            if ($updatedMessage->getNumRows() == 1) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    //delete by message id
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
}