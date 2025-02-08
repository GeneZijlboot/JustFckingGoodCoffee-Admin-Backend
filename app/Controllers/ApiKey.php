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

    //get all Api-Key's
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

    //create Api-Key
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

    //update Api-Key
    public function UpdateApiKey() {
        //define variables
        $message = null;
        $data = [
            'id' => $this->request->getPostGet('id'),
            'provider' => $this->request->getPostGet('provider'),
            'public_key' => $this->request->getPostGet('public_key'),
            'secret_key' => $this->request->getPostGet('secret_key'),
        ];

        //getsession
        $session = session();
        $currentUser = $session->get('currentUser');

        if ($status = (isset($currentUser) && $currentUser["user_role_id"] == 1)) { // check if a user is logged in and if admin
            //insert new message
            if ($status = $this->apiKeysModel->updateApiKey($data)) {
                $message = 'succesfully.updated.api_key';
            } else {
                $message = 'failed.to.update.api_key';
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

    //delete Api-Key
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

    //search the Api-Key's table over every column in the database
    public function searchCrudTable() {
        //define variables
        $message = null;
        $data = [
            'search_param' => $this->request->getPostGet('search_param'),
        ];

        //get all the data based on the serach_param
        if ($status = $this->apiKeysModel->getBySearchParam($data['search_param'])) {
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