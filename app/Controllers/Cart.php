<?php
namespace App\Controllers;

//define models
use App\Models\Carts;

class Cart extends BaseController
{
    //constructor function
    public function __construct() {
        //users model
        $this->cartsModel = model(Carts::class);
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
            if ($status = $this->cartsModel->getAll($data)) {
                foreach ($data as &$cart) { // Use a reference to modify the actual array element
                    $cart['cart_user'] = $cart['user_id'] . ' - ' . $cart['first_name'] . ' ' . $cart['last_name'];
                    $cart['cart_product'] = $cart['product_id'] . ' - ' . $cart['product_name'];
                    $cart['controller'] = 'Cart';
                }
                unset($cart); // Unset the reference after the loop to avoid unintended side effects
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