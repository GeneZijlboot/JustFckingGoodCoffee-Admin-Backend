<?php

namespace App\Controllers;
 
//define models
use App\Models\ProductVariants;

class ProductVariant extends BaseController
{
    //constructor function
    public function __construct() {
        //products Model
        $this->productVariant = model(ProductVariants::class);
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
            if ($status = $this->productVariant->getAll($data)) {
                foreach ($data as &$product_variant) { // Use a reference to modify the actual array element
                    $product_variant['product_variant_name'] = $product_variant['product_id'] . ' - ' . $product_variant['product_name'];
                    $product_variant['controller'] = 'ProductVariant';
                }
                unset($product_variant); // Unset the reference after the loop to avoid unintended side effects
            
                //define table headers
                $data['field_headers'] = [
                    '#',
                    'Product',
                    'Weight',
                    'Price',
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

    //search the messages table over every column in the datbase
    public function searchCrudTable() {
        //define variables
        $message = null;
        $data = [
            'search_param' => $this->request->getPostGet('search_param'),
        ];

        //get all the data based on the serach_param
        if ($status = $this->ProductVariant->getBySearchParam($data['search_param'])) {
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

    //create Product Variant
    public function createProductVariant() {
        //define variables
        $message = null;
        $data = [
            'product_id' => $this->request->getPostGet('product_id'),
            'product_types' => json_decode($this->request->getPostGet('product_types'), true),
        ];

        foreach ($data['product_types'] as $product_type) {
            $newProductType['product_id'] = $data['product_id'];
            //set price
            $newProductType['price'] = $product_type['price'];
        
            //check if 'weight' is set (since it could be NULL or missing in some cases)
            if (isset($product_type['weight']) && is_array($product_type['weight'])) {
                //set weight - assuming 'value' is what you want
                $newProductType['weight'] = $product_type['weight']['value'];
            } else {
                $newProductType['weight'] = NULL;
            }
            
            //post to product_variants table
            if (!($newProductType['weight'] == NULL || $newProductType['price'] == NULL)) {
                if ($status = $this->productVariant->insertProductVariant($newProductType)) {
                    $message = 'succesfully.inserting.product_variant';
                } else {
                    $message = 'error.inserting.product_variant';
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

    public function deleteProductVariant() {
        //define variables
        $message = null;
        $data = [
            'id' => $this->request->getPostGet('id'),
        ];

        //getsession
        $session = session();
        $currentUser = $session->get('currentUser');
    
        if ($status = (isset($currentUser) && $currentUser["user_role_id"] == 1)) { // check if a user is logged in and if admin
            if ($status = $this->productVariant->DeleteById($data)) {
                $message = 'succesfully.deleted.product_variant';
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