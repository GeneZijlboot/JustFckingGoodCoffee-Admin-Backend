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

    //get all rows based on the given searchValue
    public function getBySearchParam(&$searchValue) {
        if (!empty($searchValue)) {
            $this->builder->groupStart()
                    ->orLike('roles.id', $searchValue)
                    ->orLike('roles.name', $searchValue)
                    ->groupEnd();
        }
        
        $rolesResult = $this->builder->get();

        if ($rolesResult->getNumRows() > 0) {
            $searchValue = $rolesResult->getResultArray();
            return true;
        } else {
            return false;
        }
    }

    //insert given Role
    public function insertRole($data) {
        // Check if a role with the same name already exists
        $existingRole = $this->builder
                             ->where('name', $data['name'])
                             ->get();
    
        // If a match is found, return false
        if ($existingRole->getNumRows() > 0) {
            return false;
        }
    
        // Insert the new role
        $this->builder->insert($data);
    
        // Retrieve the newly created role to confirm
        $newRole = $this->builder
                        ->where('name', $data['name'])
                        ->get();
    
        // If the new role is retrieved successfully, return true
        if ($newRole->getNumRows() > 0) {
            return true;
        }
    
        // Default return false in case of any issues
        return false;
    }
    
    //update a message
    public function updateRole($data) {
        $newRole = $this->builder
                                ->where('id', $data['id'])
                                ->get();

        //check if the message exists
        if ($newRole->getNumRows() == 1) {
            //update the message
            $this->builder
                    ->where('id', $data['id'])
                    ->update(['name' => $data['name']]);
            
            $updatedRole = $this->builder
                    ->where(['id' => $data['id'], 'name' => $data['name']])
                    ->get();
                    
            if ($updatedRole->getNumRows() == 1) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    //delete by id
    public function DeleteById($role_id) {
        // Check if the user exists
        $roleResult = $this->builder()
                           ->where('id', $role_id)
                           ->get();
    
        if ($roleResult->getNumRows() > 0) {
            // User exists, proceed to delete
            $this->builder()
                 ->where('id', $role_id)
                 ->delete();
    
            return true; // Successfully deleted
        } else {
            return false; // User not found
        }
    }

    //get role options
    public function getOptions(&$data = []) {
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