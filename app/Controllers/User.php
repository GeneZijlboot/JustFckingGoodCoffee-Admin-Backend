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

        //define the session
        $session = session();
        $cartSession = $session->get('cart_data');
    
        //cefine url variables
        $email = $this->request->getPostGet('email');
        $password = $this->request->getPostGet('password');

        // Send variables to model and attempt login
        if ($status = $this->usersModel->login($email, $password, $data)) {
            $message = 'login.correct';
    
            // Set session data for authenticated user
            $session->set('authenticated', true);
            $session->set('currentUser', $data); // Store currentUser data
    
            $currentUser = $session->get('currentUser');
    
            // Check if cart session has items and add user_id to each item
            if (isset($currentUser) && isset($cartSession)) {
                foreach ($cartSession as &$sessionItem) { // Use reference to modify each item
                    $sessionItem['user_id'] = $currentUser['id']; // Add user_id to each item
                }
                // Update session with modified cart data
                $session->set('cart_data', $cartSession);
                $cartSession = $session->get('cart_data'); // Get the updated session data
    
                // Insert each cart item into the database with required fields
                foreach ($cartSession as $item) {
                    // Prepare cart data array
                    $cart_data = [
                        'user_id'    => $item['user_id'],
                        'product_id' => $item['product_id'],
                        'variant_id' => $item['variant_id'],
                        'quantity'   => $item['quantity']
                    ];
    
                    //check if item already exist in database
                    if ($status = $this->cartsModel->updateQuantityWhenInCart($cart_data)) { //item already exists in db, so update its quantity
                        $message = 'succesfully.added.product.to.cart';
                    } else {
                        if ($status = $this->cartsModel->insertProductToCart($cart_data)) { //item does not exist in db, so insert it in db
                            $message = 'succesfully.added.product.to.cart';
                        }
                    }
                }
            }
        } else {
            $message = 'login.incorrect';
            $data = [];
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
    public function register() {
        //set parameters
        $message = null;
        $data = [
            'first_name' => $this->request->getPostGet('first_name'),
            'last_name' => $this->request->getPostGet('last_name'),
            'email' => $this->request->getPostGet('email'),
            'password' => $this->request->getPostGet('password'),
            'password_confirm' => $this->request->getPostGet('password_confirm'),
        ];

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

    //forgot password function
    public function forgotPassword() {
        //set parameters
        $message = null;
        $data = [];
        $token = null;

        //getting email
        $user_email = $this->request->getPostGet('email');

        //define token

        //create a random generated token on column by email
        if ($status = $this->usersModel->createToken($user_email, $token)) {
            //define $email
            $email = service('email');
            
            if (isset($token)) {
                //defining properties
                $email->setFrom('justfckinggoodcoffee@gmail.com', 'JustFuckingGoodCoffee');
                $email->setTo($user_email);
                $email->setSubject('Reset password | JustFuckingGoodCoffee', 'JustFuckingGoodCoffee');
                $email->setMessage('<p>Click <a href="https://justfckinggoodcoffee.com/page/reset_password/' . $token . '">here</a> on this link to reset your password</p>');
            }
            // https://justfckinggoodcoffee.com/

            // check if email is send
            if ($status = $email->send()) {
                $message = 'email.succesfully.send';
            } else {
                $message = 'error.sending.email';
            }
        } else {
            $message = 'user.doesnt.exist';
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

    //reset password function
    public function resetPassword() {
        //define variables
        $message = null;

        //define data
        $data = [
            'token' => $this->request->getPostGet('token'),
            'email' => $this->request->getPostGet('email'),
            'password' => $this->request->getPostGet('password'),
            'password_confirm' => $this->request->getPostGet('password_confirm'),
        ];

        //validation rules
        $rules = [
            'password' => 'required|max_length[255]|min_length[10]',
            'password_confirm' => 'required|max_length[255]|matches[password]',
        ];

        //validateDate does the validation check
        $validation = $this->validateData($data, $rules);

        //checks if inputted data exists in the database
        $arrCheckEmail = [
            'email' => $data['email'],
        ];

        if ($status = $this->usersModel->checkIfEmailExists($arrCheckEmail)) {
            //email exists > validation check
            if ($status = $validation === true) {
                //define password
                $password = [
                    'password' => password_hash($data['password'], PASSWORD_DEFAULT), //hash password
                ];

                //define token
                $token = [
                    'token' => $data['token'],
                ];

                //passwords are correct so go on and change the password matching the email
                if ($status = $this->usersModel->changePassword($arrCheckEmail, $password, $token)) {
                    $message = 'password.changed.succesfully';
                } else {
                    $message = 'error.changing.password';
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
                        $message = $error_msg;
                    }
                }
            }
        } else {
            $message = "email.not.valid";
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

    //function to save billing infromation
    public function saveBillingInformation() {
        //define parameters
        $message = null;
        $data = [
            'phone_number' => $this->request->getPostGet('phone_number'),
            'street_name' => $this->request->getPostGet('street_name'),
            'house_number' => $this->request->getPostGet('house_number'),
            'city' => $this->request->getPostGet('city'),
            'zipcode' => $this->request->getPostGet('zipcode'),
        ];
     
        //getsession
        $session = session();
        $currentUser = $session->get('currentUser');

        if ($status = $this->usersModel->insertBillingInformation($data, $currentUser["id"])) {
            $message = 'succesfully.updated.billing.information';
        } else {
            $message = 'failed.to.update.billing.information';
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

    //function to check if billing info exists
    public function checkIfBillingAddressExists() {
        //define parameters
        $message = null;
        $data = [];

        //get currensession
        $session = session();
        $currentUser = $session->get('currentUser');

        //check if currentUser is logged in
        if ($status = isset($currentUser)) {
            //check if the currentUsers billing adress is filled in
            if ($status = $this->usersModel->checkCurrentUsersBillingAddress($currentUser["id"])) { //all information is filled in!
                $message = 'billing.info.found';
            } else {
                $message = 'billing.address.is.missing';
            }
        } else { //user not logged in so unable to check out
            $message = 'user.not.logged.in';
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
