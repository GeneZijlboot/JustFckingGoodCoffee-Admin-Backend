<?php

namespace App\Controllers;
 
//define models
use App\Models\Products;
use App\Models\Messages;
use App\Models\ProductVariants;

class Product extends BaseController
{
    //constructor function
    public function __construct() {
        //products Model
        $this->productsModel = model(Products::class);
        $this->messagesModel = model(Messages::class);
        $this->productVariantsModel = model(ProductVariants::class);
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
            if ($status = $this->productsModel->getAll($data)) {
                foreach ($data as &$product) { // Use a reference to modify the actual array element
                    $product['controller'] = 'Product';
                }
                unset($product); // Unset the reference after the loop to avoid unintended side effects

                //define table headers
                $data['field_headers'] = [
                    '#',
                    'Name',
                    'Image',
                    'Infobar image',
                    'Roast type',
                    'Origin',
                    'Description',
                    'Data',
                    'Information',
                    'Reviews',
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

    public function createProduct() {
        //define variables
        $message = null;
        $data = [
            'product_name' => $this->request->getPostGet('product_name'),
            'image_url' => '/images/' . basename(parse_url($this->request->getPostGet('product_image_url'), PHP_URL_PATH)),
            'infobar_image_url' => '/images/' . basename(parse_url($this->request->getPostGet('infobar_image_url'), PHP_URL_PATH)),
            'roast_type' => $this->request->getPostGet('roast_type'),
            'origin' => $this->request->getPostGet('origin'),
            'product_types' => json_decode($this->request->getPostGet('product_types'), true),
            'translations' => json_decode($this->request->getPostGet('translations'), true),
        ];

        //getsession
        $session = session();
        $currentUser = $session->get('currentUser');

        //check if a user is logged in and if admin
        if ($status = (isset($currentUser) && $currentUser["user_role_id"] == 1)) {
            //new array for product
            $newProduct = [
                'name' => $data['product_name'],
                'image_url' => $data['image_url'],
                'infobar_image_url' => $data['infobar_image_url'],
                'roast_type' => $data['roast_type'],
                'origin' => $data['origin'],
                'description' => 'product_details.' . strtolower(str_replace(' ', '_', $data['product_name'])) . '.description',
                'data' => 'product_details.' . strtolower(str_replace(' ', '_', $data['product_name'])) . '.data',
                'information' => 'product_details.' . strtolower(str_replace(' ', '_', $data['product_name'])) . '.information',
                'reviews' => 'product_details.' . strtolower(str_replace(' ', '_', $data['product_name'])) . '.reviews',
            ];

            //post to product table
            if ($status = $this->productsModel->insertProduct($newProduct)) {
                //new array for product type
                $newProductType = [
                    'product_id' => $newProduct['id'],
                ];
                foreach ($data['product_types'] as $product_type) {
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
                        if (!($this->productVariantsModel->insertProductVariant($newProductType))) {
                            $message = 'error.inserting.product_variant';
                        }
                    }
                }
                //new array for messages
                $fields = ['description', 'data', 'information'];

                // Loop through translations
                foreach ($data['translations'] as $message) {
                    foreach ($fields as $field) {
                        // Check if the field has a value
                        if (!empty($message[$field])) {
                            // Build the message array
                            $newMessage = [
                                'language' => $message['selectedLanguage'],
                                'name' => 'product_details.' . $data['product_name'] . '.' . $field,
                                'message' => $message[$field],
                            ];

                            // Attempt to insert the message
                            if (!($this->messagesModel->insertMessage($newMessage))) {
                                $message = 'error.inserting.message';
                            }
                        }
                    }
                }
            } else {
                $message = 'error.inserting.product.name.already.exists';
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

    public function deleteProduct() {
        //define variables
        $message = null;
        $data = [
            'id' => $this->request->getPostGet('id'),
        ];

        //getsession
        $session = session();
        $currentUser = $session->get('currentUser');
    
        if ($status = (isset($currentUser) && $currentUser["user_role_id"] == 1)) { // check if a user is logged in and if admin
            if ($status = $this->productsModel->DeleteById($data)) {
                $message = 'succesfully.deleted.role';
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

    public function saveImage() {
        $message = [];
    
        //define file save directory
        $savePath = ROOTPATH . 'public/images';
    
        //ensure directory exists
        if (!is_dir($savePath)) {
            mkdir($savePath, 0755, true);
        }
    
        //define validation rules
        $validationRules = [
            'productImageFile' => 'uploaded[productImageFile]|max_size[productImageFile,2048]|ext_in[productImageFile,png,jpg,jpeg]|is_image[productImageFile]',
            'infobarImageFile' => 'uploaded[infobarImageFile]|max_size[infobarImageFile,2048]|ext_in[infobarImageFile,png,jpg,jpeg]|is_image[infobarImageFile]',
        ];
    
        //retrieve files
        $data = [
            'productImageFile' => $this->request->getFile('productImageFile'),
            'infobarImageFile' => $this->request->getFile('infobarImageFile'),
        ];

        //validate files
        if ($status = $this->validate($validationRules)) {
            foreach ($data as $file) {
                $fileName = $file->getName();
                //check if the given file exists by file name
                if ($status = file_exists($savePath . DIRECTORY_SEPARATOR . $fileName)) { //exists
                    $message = "Admin Backend - " . $fileName . " alread exist in: " . $savePath;
                } else { //doesnt exists -> move give file there
                    if ($status =  $file->move($savePath, $fileName)) {
                        $message = "Admin Backend - succesfully moved: " . $fileName . " to " . $savePath;
                    } else {
                        $message = "Admin Backend - Unable to move: " . $fileName . " to " . $savePath;
                    }
                }
            }
        } else {
            $validationErrors = $this->validator->getErrors();
            $message = implode("\n", $validationErrors);
        }

        //define response data
        $response_data = [
            'status' => $status,
            'data' => $data,
            'message' => $message,
        ];
    
        //return response in JSON format
        return $this->response->setJSON($response_data);
    }
}