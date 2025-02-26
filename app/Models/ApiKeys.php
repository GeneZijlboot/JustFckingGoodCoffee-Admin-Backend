<?php

namespace App\Models;

use CodeIgniter\Model;

class ApiKeys extends Model
{
    //constructor fucntion
    public function __construct() {
        // Initialize database connection and assign the query builder to the users table
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('api_keys');
    }

    //get all users
    public function getAll(&$data = []) {
        $apiKeyResult = $this->builder
                                ->select('api_keys.id, api_keys.integration_id, api_keys.provider, api_keys.public_key, api_keys.secret_key')
                                ->get();
        //check if there are results and return them
        if ($apiKeyResult->getNumRows() > 0) {
            $data = $apiKeyResult->getResultArray();
            return true;
        } else {
            return false;
        }
    }

    //get all rows based on the given searchValue
    public function getBySearchParam(&$searchValue) {
        if (!empty($searchValue)) {
            $this->builder->groupStart()
                    ->orLike('api_keys.id', $searchValue)
                    ->orLike('api_keys.provider', $searchValue)
                    ->orLike('api_keys.public_key', $searchValue)
                    ->orLike('api_keys.secret_key', $searchValue)
                    ->groupEnd();
        }
        
        $messageResult = $this->builder->get();

        if ($messageResult->getNumRows() > 0) {
            $searchValue = $messageResult->getResultArray();
            return true;
        } else {
            return false;
        }
    }

    //insert given Api-Key
    public function insertApiKey($data) {
        //check if a role with the same name already exists
        $existingApiKey = $this->builder
                                ->where('provider', $data['provider'])
                                ->get();
    
        //if a match is found, return false
        if ($existingApiKey->getNumRows() > 0) {
            return false;
        }
    
        //insert the new role
        $this->builder->insert($data);
    
        // Retrieve the newly created role to confirm
        $newApiKey = $this->builder
                        ->where('provider', $data['provider'])
                        ->get();
    
        //if the new role is retrieved successfully, return true
        if ($newApiKey->getNumRows() > 0) {
            return true;
        }
    
        //default return false in case of any issues
        return false;
    }

    //update an Api-Key
    public function updateApiKey($data) {
        $newApiKey = $this->builder
                                ->where('id', $data['id'])
                                ->get();

        //check if the message exists
        if ($newApiKey->getNumRows() == 1) {
        //update the message
            $this->builder
                    ->where('id', $data['id'])
                    ->update(['provider' => $data['provider'], 'integration_id' => $data['integration_id'], 'public_key' => $data['public_key'], 'secret_key' => $data['secret_key']]);
            
            $updatedApiKey = $this->builder
                    ->where(['id' => $data['id'], 'integration_id' => $data['integration_id'], 'provider' => $data['provider'], 'public_key' => $data['public_key'], 'secret_key' => $data['secret_key']])
                    ->get();
                    
            if ($updatedApiKey->getNumRows() == 1) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    //delete by Api-Key id
    public function DeleteById($api_key_id) {
        // Check if the user exists
        $apiKeysResult = $this->builder()
                        ->where('id', $api_key_id)
                        ->get();
    
        if ($apiKeysResult->getNumRows() > 0) {
            // User exists, proceed to delete
            $this->builder()
                ->where('id', $api_key_id)
                ->delete();
    
            return true; // Successfully deleted
        } else {
            return false; // User not found
        }
    }

    //get tinymce key
    public function getTinyMCEKey(&$data) {
        $result = $this->builder
                ->select('public_key')
                ->where('provider', $data["provider"])
                ->get(); //get all matching rows
    
        //check if there are results and return the key value
        if ($result->getNumRows() > 0) {
            //set key value equal to data for data-flow-back
            $resultArray = $result->getResultArray();
            $data["api_key"] = $resultArray[0]["public_key"];

            return true;
        } else {
            return false;
        }
    }

    //get sendcloud data
    public function getSendCloudKeys(&$data) {
        $result = $this->builder
                ->select('integration_id, public_key, secret_key')
                ->where('provider', $data["provider"])
                ->get(); //get all matching rows

        //check if there are results and return the key value
        if ($result->getNumRows() > 0) {
            //set key value equal to data for data-flow-back
            $resultArray = $result->getResultArray();
            $data["api_key"] = $resultArray[0]["public_key"] . ':' . $resultArray[0]["secret_key"];
            $data["integration_id"] = $resultArray[0]["integration_id"];
            return true;
        } else {
            return false;
        }
    }
}