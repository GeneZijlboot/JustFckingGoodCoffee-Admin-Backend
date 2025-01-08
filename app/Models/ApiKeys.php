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
                                ->select('api_keys.id, api_keys.provider, api_keys.public_key, api_keys.secret_key')
                                ->get();
        //check if there are results and return them
        if ($apiKeyResult->getNumRows() > 0) {
            $data = $apiKeyResult->getResultArray();
            return true;
        } else {
            return false;
        }
    }
}