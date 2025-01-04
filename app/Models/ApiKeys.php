<?php

namespace App\Models;

use CodeIgniter\Model;

class apikeys extends Model
{
    //constructor function
    public function __construct() {
        // Initialize database connection and assign the query builder to the users table
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('api_keys');
    }

    //get public_key on (where) provider -> name
    public function getMollieKeys(&$data) {
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

    public function getSendCloudKeys(&$data) {
        $result = $this->builder
                ->select('public_key, secret_key')
                ->where('provider', $data["provider"])
                ->get(); //get all matching rows

        //check if there are results and return the key value
        if ($result->getNumRows() > 0) {
            //set key value equal to data for data-flow-back
            $resultArray = $result->getResultArray();
            // var_dump($resultArray[0]["public_key"]);
            // var_dump($resultArray[0]["secret_key"]);die;
            $data["api_key"] = $resultArray[0]["public_key"] . ':' . $resultArray[0]["secret_key"];

            return true;
        } else {
            return false;
        }
    }
}