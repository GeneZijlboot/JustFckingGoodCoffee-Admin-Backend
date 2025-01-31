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
            if ($status = $this->apiKeysModel->getAll($data)) {
                foreach ($data as &$api_key) { // Use a reference to modify the actual array element
                    $api_key['controller'] = 'ApiKey';
                }
                unset($api_key); // Unset the reference after the loop to avoid unintended side effects

                //define table headers
                $data['field_headers'] = [
                    '#',
                    'Provider',
                    'Public Key',
                    'Secret Key',
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

    public function createApiKey() {
        //define variables
        $message = null;
        $data = [
            'provider' => $this->request->getPostGet('provider'),
            'public_key' => $this->request->getPostGet('public_key'),
            'secret_key' => $this->request->getPostGet('secret_key'),
        ];

        //insert new role
        if ($status = $this->apiKeysModel->insertApiKey($data)) {
            $message = 'succesfully.created.api-key';
        } else {
            $message = 'api-key.already.exists';
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

    public function deleteApiKey() {
        //define variables
        $message = null;
        $data = [
            'id' => $this->request->getPostGet('id'),
        ];

        //getsession
        $session = session();
        $currentUser = $session->get('currentUser');
    
        if ($status = (isset($currentUser) && $currentUser["user_role_id"] == 1)) { // check if a user is logged in and if admin
            if ($status = $this->apiKeysModel->DeleteById($data)) {
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