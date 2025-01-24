<?php

namespace App\Controllers;
 
//define models
use App\Models\Products;
use App\Models\Messages;

class Product extends BaseController
{
    //constructor function
    public function __construct() {
        //products Model
        $this->productsModel = model(Products::class);
        $this->messagesModel = model(Messages::class);
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
            'name' => $this->request->getPostGet('name'),
            'image_url' => '/images/' . $this->request->getPostGet('image_url'),
            'infobar_image_url' => '/images/' . $this->request->getPostGet('infobar_image_url'),
            'roast_type' => $this->request->getPostGet('roast_type'),
            'origin' => $this->request->getPostGet('origin'),
            'description' => $this->request->getPostGet('description'),
            'data' => $this->request->getPostGet('data'),
            'information' => $this->request->getPostGet('information'),
            'language' => $this->request->getPostGet('language'),
        ];

         //getsession
         $session = session();
         $currentUser = $session->get('currentUser');
 
         //check if a user is logged in and if admin
         if ($status = (isset($currentUser) && $currentUser["user_role_id"] == 1)) {

            //build array for product to be inserted
            $newProduct = [
                'name' => $this->request->getPostGet('name'),
                'image_url' => '/images/' . $this->request->getPostGet('image_url'),
                'infobar_image_url' => '/images/' . $this->request->getPostGet('infobar_image_url'),
                'roast_type' => $data['roast_type'],
                'origin' => $data['origin'],
                'description' => 'product_details.' . strtolower(str_replace(' ', '_', $data['name'])) . '.description',
                'data' => 'product_details.' . strtolower(str_replace(' ', '_', $data['name'])) . '.data',
                'information' => 'product_details.' . strtolower(str_replace(' ', '_', $data['name'])) . '.information',
            ];

            //insert new product in product table
            if ($status = $this->productsModel->insertProduct($newProduct)) {
                $message = 'succesfully.inserted.product';

                // Prepare messages
                $keys = ['description', 'data', 'information'];
                $newMessages = [];

                foreach ($keys as $key) {
                    $newMessages[] = [
                        'name' => 'product_details.' . strtolower(str_replace(' ', '_', $data['name'])) . '.' . $key,
                        'language' => $data['language'],
                        'message' => $this->request->getPostGet($key),
                    ];
                }

                foreach($newMessages as $newMessage) {
                    if ($status = $this->messagesModel->insertMessage($newMessage)) {
                        $message = 'succesfully.inserted.product';
                    } else {
                        $message = 'unsuccesfully.inserted.product';
                    }
                }
            } else {
                $message = 'unsuccesfully.inserted.product';
                $data = [];
            }
         } else {
             $message = 'not.logged.in';
         }

        /* 
            TODO::
            . in frontend wright the request to insert the 2 images in webshop project under public/assets/filename -> if this is correct, only then do a request to here and do these steps:
            . define filepath for both images
            . define the product data ( without the language )
            . insert it, if thats correct make the array for the translations, loop do description, data, information
            . then after that send back an okay status:
        */

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
        //define parameters
        $message = [];
        $data = [
            'productImageFile' => $this->request->getFile('productImageFile'),
            'infobarImageFile' => $this->request->getFile('infobarImageFile'),
        ];

        //define file save directory
        $savePath = ROOTPATH . 'public/images';
        
        //ensure directory exists
        if (!is_dir($savePath)) {
            mkdir($savePath, 0755, true); //create directory with the correct rights if it doesn't exist
        }
        
        foreach ($data as $file) {
            $fileName = $file->getName();
            /*
                TODO::
                sometimes when a image is really large it cannot be uploaded so make some ci4 validation rules for it so a user understands what goes wrong.

                and do make it asynchronously....... otherwise the requests go all at once, at you get 3 error messages through eah other..

                -> build on localhost make controller for dev and prod.??
            */
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