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
                
                //define table headers
                $data['field_headers'] = [
                    '#',
                    'Role',
                    'First name',
                    'Last name',
                    'Email',
                    'Phone number',
                    'Street name',
                    'House number',
                    'City',
                    'Zipcode',
                    'Subscription',
                    'Member from',
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

    //delete by id
    public function deleteUser() {
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

    //register function 
    public function createUser() {
        //set parameters
        $message = null;
        $data = [
            'first_name' => $this->request->getPostGet('first_name'),
            'last_name' => $this->request->getPostGet('last_name'),
            'email' => $this->request->getPostGet('email'),
            'phone_number' => $this->request->getPostGet('phone_number'),
            'password' => $this->request->getPostGet('password'),
            'password_confirm' => $this->request->getPostGet('password_confirm'),
        ];

        //get optional fields
        $optionalFields = ['city', 'zipcode', 'street_name', 'house_number'];
        
        foreach ($optionalFields as $field) {
            $value = $this->request->getPostGet($field);
            if (isset($value)) {
                $data[$field] = $value;
            }
        }

        //validation rules
        $rules = [
            'password' => 'required|max_length[255]|min_length[10]',
            'password_confirm' => 'required|max_length[255]|matches[password]',
            'email' => 'required|max_length[254]|valid_email',
        ];

        //validateDate does the validation check
        $validation = $this->validateData($data, $rules);

        //validation succes
        if ($status = $validation === true) {
            //checks if inputted data exists in the database
            $arrCheckEmail = [
                'email' => $data['email'],
            ];

            //checks if inputted data exists in the database
            if ($status = !$this->usersModel->checkIfEmailExists($arrCheckEmail)) {
                //user doesnt exist yet so hash password
                $createNewUser = [
                    'user_role_id' => 2, //set the user_role_id standard to 2 -> 2 = customer
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'email' => $data['email'],
                    'password' => password_hash($data['password'], PASSWORD_DEFAULT), //hash password
                ];

                // Add optional fields to the createNewUser array if they are set
                foreach ($optionalFields as $field) {
                    if (isset($data[$field])) {
                        $createNewUser[$field] = $data[$field];
                    }
                }

                if ($status = $this->usersModel->createUser($createNewUser)) {
                    $message = 'succes.creating.user';
                } else {
                    $message = 'error.creating.user';
                }
            } else {
                //user already exists
                $data = [];
                $message = 'user.already.exists'; 
            }
            
        } else { //validation error
            // Check if both password fields exist
            if (!isset($data['password']) || !isset($data['password_confirm'])) {
                $error_msg = 'password.fields.missing';  // error if password fields are missing
            } else {
                // Check if passwords match
                if ($data['password'] !== $data['password_confirm']) {
                    $error_msg = 'password.dont.match';  // error if passwords don't match
                }
                // Check if password is longer than 10 characters
                else if (strlen($data['password']) < 10) {
                    $error_msg = 'password.too.short';  // error if password is too short
                }

                // If there was any validation error, set the message
                if (isset($error_msg)) {
                    $data = [];  // Optional: clear $data or keep it if needed
                    $message = $error_msg;
                }
            }
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

    //search the messages table over every column in the datbase
    public function searchCrudTable() {
        //define variables
        $message = null;
        $data = [
            'search_param' => $this->request->getPostGet('search_param'),
        ];

        //get all the data based on the serach_param
        if ($status = $this->usersModel->getBySearchParam($data['search_param'])) {
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
