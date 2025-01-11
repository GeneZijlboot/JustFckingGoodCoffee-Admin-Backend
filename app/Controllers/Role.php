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

    public function deleteRole() {
        //define variables
        $message = null;
        $data = [];

        //define url parameter
        $user_id = $this->request->getPostGet('id');

        //getsession
        $session = session();
        $currentUser = $session->get('currentUser');
    
        if ($status = (isset($currentUser) && $currentUser["user_role_id"] == 1)) { // check if a user is logged in and if admin
            if ($status = $this->rolesModel->DeleteById($user_id)) {
                $message = 'succesfully.deleted.user';
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
}