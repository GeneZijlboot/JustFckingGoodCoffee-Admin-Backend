<?php

namespace App\Controllers;
 
//define models
use App\Models\ProductVariants;

class ProductVariant extends BaseController
{
    //constructor function
    public function __construct() {
        //products Model
        $this->ProductVariant = model(ProductVariants::class);
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
            if ($status = $this->ProductVariant->getAll($data)) {
                foreach ($data as &$product_variant) { // Use a reference to modify the actual array element
                    $product_variant['product_variant_name'] = $product_variant['product_id'] . ' - ' . $product_variant['product_name'];
                    $product_variant['controller'] = 'ProductVariant';
                }
                unset($product_variant); // Unset the reference after the loop to avoid unintended side effects
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