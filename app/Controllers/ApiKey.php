<?php

namespace App\Controllers;
 
//define models
use App\Models\ApiKeys;

class ApiKey extends BaseController
{
    //constructor function
    public function __construct() {
        //products Model
        $this->apiKeysModel = model(ApiKeys::class);
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
            if (!$status = $this->apiKeysModel->getAll($data)) {
                foreach ($data as &$api_key) { // Use a reference to modify the actual array element
                    $api_key['controller'] = 'Cart';
                }
                unset($api_key); // Unset the reference after the loop to avoid unintended side effects
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