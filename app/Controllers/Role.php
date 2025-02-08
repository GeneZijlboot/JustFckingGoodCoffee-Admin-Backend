<?php

namespace App\Controllers;
 
//define models
use App\Models\Roles;

class Role extends BaseController
{
    //constructor function
    public function __construct() {
        //products Model
        $this->rolesModel = model(Roles::class);
    }

    //get all users
    public function getAll() {
        //define variables
        $message = null;
        $data = [];

        //getsession
        $session = session();
        $currentUser = $session->get('currentUser');

        if ($status = (isset($currentUser) && $currentUser["user_role_id"] == 1)) { // check if a user is logged in and if admin
            if ($status = $this->rolesModel->getAll($data)) {
                foreach ($data as &$role) { // Use a reference to modify the actual array element
                    $role['controller'] = 'Role';
                }
                unset($role); // Unset the reference after the loop to avoid unintended side effects

                 //define table headers
                 $data['field_headers'] = [
                    '#',
                    'Name',
                    '', //for the CRUD icons
                ];
            }
        } else {
            $message = 'not.logged.in';
        }

        //define response data
        $response_data = [
            'status' => $status,
            'data' => $data,
            'message' => $message
        ];

        //return response back to frontend -> in JSON format
        return $this->response->setJSON($response_data);
    }

    //create role
    public function createRole() {
        //define variables
        $message = null;
        $data = [
            'name' => $this->request->getPostGet('name'),
        ];

        //insert new role
        if ($status = $this->rolesModel->insertRole($data)) {
            $message = 'succesfully.created.role';
        } else {
            $message = 'role.already.exists';
        }

        //define response data
        $response_data = [
            'status' => $status,
            'data' => $data,
            'message' => $message
        ];

        //return response back to frontend -> in JSON format
        return $this->response->setJSON($response_data);
    }

    //update role
    public function UpdateRole() {
        //define variables
        $message = null;
        $data = [
            'id' => $this->request->getPostGet('id'),
            'name' => $this->request->getPostGet('name'),
        ];

        //getsession
        $session = session();
        $currentUser = $session->get('currentUser');

        if ($status = (isset($currentUser) && $currentUser["user_role_id"] == 1)) { // check if a user is logged in and if admin
            //insert new message
            if ($status = $this->rolesModel->updateRole($data)) {
                $message = 'succesfully.updated.role';
            } else {
                $message = 'failed.to.update.role';
            }
        } else {
            $message = 'not.logged.in';
        }


        //define response data
        $response_data = [
            'status' => $status,
            'data' => $data,
            'message' => $message
        ];

        //return response back to frontend -> in JSON format
        return $this->response->setJSON($response_data);
    }

    //delete role on id
    public function deleteRole() {
        //define variables
        $message = null;
        $data = [
            'id' => $this->request->getPostGet('id'),
        ];

        //getsession
        $session = session();
        $currentUser = $session->get('currentUser');
    
        if ($status = (isset($currentUser) && $currentUser["user_role_id"] == 1)) { // check if a user is logged in and if admin
            if ($status = $this->rolesModel->DeleteById($data)) {
                $message = 'succesfully.deleted.role';
            }
        } else {
            $message = 'not.logged.in';
        }

        //define response data
        $response_data = [
            'status' => $status,
            'data' => $data,
            'message' => $message
        ];

        //return response back to frontend -> in JSON format
        return $this->response->setJSON($response_data);
    }

    public function getOptions() {
        // Initialize response variables
        $message = null;
        $data = [];
        $status = false;
    
        // Get session and current user
        $session = session();
        $currentUser = $session->get('currentUser');
    
        // Check if a user is logged in and is an admin
        if (isset($currentUser) && $currentUser["user_role_id"] == 1) {
            // Fetch role options
            $status = $this->rolesModel->getOptions($data);
            if ($status) {
                // Modify role names
                foreach ($data as &$role) { 
                    $role['name'] = $role['id'] . ' - ' . $role['name'];
                }
                unset($role); // Best practice: Unset reference after foreach loop
                $message = 'successfully.got.role.options';
            } else {
                $message = 'failed.to.get.role.options';
            }
        } else {
            $message = 'not.logged.in.or.not.admin';
        }
    
        // Define response data
        $response_data = [
            'status' => $status,
            'data' => $data,
            'message' => $message,
        ];
    
        // Return response back to frontend in JSON format
        return $this->response->setJSON($response_data);
    }

    //search the messages table over every column in the datbase
    public function searchCrudTable() {
        //define variables
        $message = null;
        $data = [
            'search_param' => $this->request->getPostGet('search_param'),
        ];

        //get all the data based on the serach_param
        if ($status = $this->rolesModel->getBySearchParam($data['search_param'])) {
            $message = 'succesfully.found.results';
        } else {
            $message = 'No results found for: ' . $data['search_param'];
        }

        // Define response data
        $response_data = [
            'status' => $status,
            'data' => $data, // Return grouped data
            'message' => $message
        ];
    
        // Return response back to frontend -> in JSON format
        return $this->response->setJSON($response_data);
    }
}