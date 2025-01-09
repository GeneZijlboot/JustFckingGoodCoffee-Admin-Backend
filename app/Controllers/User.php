<?php

namespace App\Controllers;

//define models
use App\Models\Users;
use App\Models\Carts;

class User extends BaseController
{
    //constructor function to initialize the model
    public function __construct() {
        //users model
        $this->usersModel = model(Users::class);

        //carts model
        $this->cartsModel = model(Carts::class);
    }

    //login function
    public function login() {
        //set parameters
        $message = null;
        $data = [];

        //define url variables
        $email = $this->request->getPostGet('email');
        $password = $this->request->getPostGet('password');

        //define the session
        $session = session();

        // Send variables to model and attempt login
        if ($status = $this->usersModel->login($email, $password, $data)) {
            // Set session data for authenticated user - Store currentUser data
            $session->set('currentUser', $data);

            $message = 'login.correct';
        } else {
            $message = 'login.incorrect';
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
    
    //logout function
    public function logout() {
        //set parameters
        $message = null;
        $data = [];

        //define the session
        $session = session();

        $session->destroy();

        //logged-out to true so authentication is false
        if ($status = isset($session)) {
            $message = 'succesfully.logged.out';
        } else {
            $message = 'error.logging.out.contact_admin';
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

    //getting session data
    public function getSession() {
        //define variables
        $message = null;
        $data = [];

        //getsession
        $session = session();
        $currentUser = $session->get('currentUser');

        // check if currentUser is defined
        if ($status = isset($currentUser)) {
            $message = 'logged.in';
            $data = $currentUser;

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

    //get all users
    public function getAll() {
        //define variables
        $message = null;
        $data = [];

        //getsession
        $session = session();
        $currentUser = $session->get('currentUser');
 
        if ($status = (isset($currentUser) && $currentUser["user_role_id"] == 1)) { // check if a user is logged in and if admin
            if ($status = $this->usersModel->getAll($data)) {
                foreach ($data as &$user) { // Use a reference to modify the actual array element
                    $user['user_role'] = $user['user_role_id'] . ' - ' . $user['role_name'];
                    $user['controller'] = 'User';
                }
                unset($user); // Unset the reference after the loop to avoid unintended side effects
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


    //delete by id
    public function DeleteById() {
            //define variables
            $message = null;
            $data = [];

            //define url parameter
            $user_id = $this->request->getPostGet('id');

            //getsession
            $session = session();
            $currentUser = $session->get('currentUser');
        
            if ($status = (isset($currentUser) && $currentUser["user_role_id"] == 1)) { // check if a user is logged in and if admin
                if ($status = $this->usersModel->DeleteById($user_id)) {
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
