<?php

namespace App\Models;

use CodeIgniter\Model;

class Products extends Model
{
    //constructor fucntion
    public function __construct() {
        // Initialize database connection and assign the query builder to the users table
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('products');
    }

    //get all users
    public function getAll(&$data = []) {
        $productResult = $this->builder
                                ->select('products.id, products.name, products.image_url, products.infobar_image_url, products.roast_type, products.origin, products.description, products.data, products.information, products.reviews')
                                ->get();
        //check if there are results and return them
        if ($productResult->getNumRows() > 0) {
            $data = $productResult->getResultArray();
            return true;
        } else {
            return false;
        }
    }

    //insert given Product
    public function insertProduct(&$data) {
        // Check if a role with the same name already exists
        $existingProduct = $this->builder
                             ->where('name', $data['name'])
                             ->get();
    
        //if a match is found, return false
        if ($existingProduct->getNumRows() > 0) {
            return false;
        }
    
        //insert the new role
        $this->builder->insert($data);
    
        // Retrieve the newly created role to confirm
        $newProduct = $this->builder
                        ->where('name', $data['name'])
                        ->get();
    
        // If the new role is retrieved successfully, return true
        if ($newProduct->getNumRows() > 0) {
            $data['id'] = $newProduct->getRow()->id;
            return true;
        }
    
        // Default return false in case of any issues
        return false;
    }

    //delete by id
    public function DeleteById($product_id) {
        // Check if the user exists
        $productExist = $this->builder()
                            ->where('id', $product_id)
                            ->get();
    
        if ($productExist->getNumRows() > 0) {
            //user exists, proceed to delete
            $this->builder()
                    ->where('id', $product_id)
                    ->delete();
    
            return true; //successfully deleted
        } else {
            return false; //user not found
        }
    }

    public function getIdByName(&$product) {
        $productResult = $this->builder
                              ->select('products.id')
                              ->where('name', $product['name'])
                              ->get();
        
        // Check if there are results and return the product ID
        if ($productResult->getNumRows() > 0) {
            // Get the first result (assumed to be the unique product)
            $productData = $productResult->getRowArray();
            $product['id'] = $productData['id'];  // Update the $product array with the ID
            return true;  // Return true if product found
        } else {
            return false;  // Return false if no product found
        }
    }
}