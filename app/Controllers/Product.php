<?php

namespace App\Controllers;
 
//define models
use App\Models\Products;
use App\Models\Users;

class Product extends BaseController
{
    //constructor function
    public function __construct() {
        //products Model
        $this->productsModel = model(Products::class);

        //users model
        $this->usersModel = model(Users::class);
    }
    
    //getCoffee function
    public function getAllProducts() {
        //define parameters
        $message = null;
        $data = [];

        if ($status = $this->productsModel->getAll($data)) {
            $message = 'getting.coffee.succesful';
        } else {
            $message = 'getting.coffee.failed';
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

    //getProduct function
    public function getSpecificProduct() {
        //define parameters
        $message = null;

        //get coffee name
        $data = $this->request->getPostGet('name');

        if ($this->request->getPostGet('weight') !== null) {
            //if it is than get the variable
            $weight = $this->request->getPostGet('weight');
        } else {
            //if its not found, keep the standart -> 250 grams
            $weight = '250';
        }

        //get details of that coffee name
        if ($status = $this->productsModel->getWhere($data, $weight, $data)) {
            $message = 'succesfully.found.product';
        } else {
            $message = 'could.not.find.product';
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

    //edit productdetails
    public function editProduct() {
        //define parameters
        $message = null;
        $data = [
            'product_id' => $this->request->getPostGet('product_id'),
            'variant_id' => $this->request->getPostGet('variant_id'),
            'name' => $this->request->getPostGet('name'),
            'origin' => $this->request->getPostGet('origin'),
            'roast_type' => $this->request->getPostGet('roast_type'),
            'description' => $this->request->getPostGet('description'),
            'price' => $this->request->getPostGet('price'),
        ];

        //get session
        $session = session();
        $currentUser = $session->get('currentUser');

        //check if someone is logged in
        if ($status = isset($currentUser)) {
            //check if user is admin
            if ($status = $this->usersModel->getUserRights($currentUser["id"])) { //user is admin
                if ($status = $this->productsModel->updateProduct($data)) {
                    $message = 'succesfully.updated.product';
                } else {
                    $message = 'failed.to.update.product';
                }
            } else { //user is not admin
                $message = 'you.need.to.be.admin.to.do.this';
            }
        } else {
            $message = 'you.need.to.be.admin.to.do.this';
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