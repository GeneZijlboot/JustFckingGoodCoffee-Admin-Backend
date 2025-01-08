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
            if (!$status = $this->rolesModel->getAll($data)) {
                //error ? doesnt matter...
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