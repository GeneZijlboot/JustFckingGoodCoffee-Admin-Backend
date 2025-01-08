<?php

namespace App\Models;

use CodeIgniter\Model;

class Roles extends Model
{
    //constructor fucntion
    public function __construct() {
        // Initialize database connection and assign the query builder to the users table
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('roles');
    }

    //get all users
    public function getAll(&$data = []) {
        $roleResult = $this->builder
                                ->select('roles.id, roles.name')
                                ->get();
        //check if there are results and return them
        if ($roleResult->getNumRows() > 0) {
            $data = $roleResult->getResultArray();
            return true;
        } else {
            return false;
        }
    }
}