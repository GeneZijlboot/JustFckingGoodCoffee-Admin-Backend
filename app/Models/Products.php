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
        $this->product_variants_builder = $this->db->table('product_variants');
    }

    public function getAll(&$data = []) {
        //get every coffee, with weight = 250gram and is pricing from the product_variants table
        $coffeeResults = $this->builder
                              ->select('products.id, products.name, products.image_url, products.roast_type, products.origin, products.description, products.discount, product_variants.weight, product_variants.price')
                              ->join('product_variants', 'products.id = product_variants.product_id')
                              ->where('product_variants.weight', '250')
                              ->get();
        
        // Check if there are results and return them
        if ($coffeeResults->getNumRows() > 0) {
            $data = $coffeeResults->getResultArray();
            return true;
        } else {
            return false;
        }
    }
    
    public function getProductPrice($product_id, $variant_id, &$price) {
        // Connect to the database
        $db = \Config\Database::connect();
        $builder = $db->table('product_variants'); // Connect to the product_variants table
    
        // Query to get the price for the specified product and variant
        $product_variants = $builder
                            ->select('price')
                            ->where('id', $variant_id)
                            ->where('product_id', $product_id)
                            ->get();
    
        // Check if there are results
        if ($product_variants->getNumRows() > 0) {
            $data = $product_variants->getRowArray();
            
            // Assign price to the passed reference variable
            $price = $data['price'];
            return true;
        } else {
            return false;
        }
    }
    
    public function getWhere($coffeeName, $weight, &$data = []) {
        //get every coffee, with weight = 250gram and is pricing from the product_variants table
        $coffeeResults = $this->builder
                                ->select('products.id, products.name, products.image_url, products.infobar_image_url, products.roast_type, products.origin, products.description, products.discount, products.data, products.information, products.reviews, product_variants.id AS variant_id, product_variants.weight, product_variants.price')
                                ->join('product_variants', 'products.id = product_variants.product_id')
                                ->where('products.name', $coffeeName)
                                ->where('product_variants.weight', $weight)
                                ->get();

        // Check if there are results and return them
        if ($coffeeResults->getNumRows() > 0) {
            $data = $coffeeResults->getResultArray();
            return true;
        } else {
            return false;
        }
    }

    public function getProductDetails(&$cart_data = []) {
        //get specific product data based on product_id and variant_id
        $coffeeResults = $this->builder
                                ->select('products.image_url, products.name, product_variants.weight, product_variants.price')
                                ->join('product_variants', 'products.id = product_variants.product_id')
                                ->where('products.id', $cart_data['product_id'])
                                ->where('product_variants.id', $cart_data['variant_id'])
                                ->get();
    
        //check if there are results and return them
        if ($coffeeResults->getNumRows() > 0) {
            // Fetch result as an associative array
            $productDetails = $coffeeResults->getRowArray();
            
            //merge the product details into cart data while keeping existing values
            $cart_data = array_merge($cart_data, $productDetails);
            return true;
        } else {
            return false;
        }
    }

    //update a specific product
    public function updateProduct(&$newProductData) {
        // Get specific product data based on pupdateProductroduct_id and variant_id
        $productResults = $this->builder
                                ->where('id', $newProductData['product_id'])
                                ->get();
    
        $product_variants_results = $this->product_variants_builder   
                                            ->where('id', $newProductData['variant_id'])
                                            ->where('product_id', $newProductData['product_id'])
                                            ->get();                   
    
        // Check if there are results and return them
        if (($productResults->getNumRows() > 0) && ($product_variants_results->getNumRows() > 0)) {
            $productResults = $productResults->getRow();
            $product_variants_results = $product_variants_results->getRow();
    
            // Update product data
            $this->builder
                ->where('id', $newProductData['product_id'])
                ->update([
                    'name' => $newProductData['name'],
                    'origin' => $newProductData['origin'],
                    'roast_type' => $newProductData['roast_type'],
                    'description' => $newProductData['description']
                ]);
    
            // Update product variant data
            $this->product_variants_builder
                ->where('id', $newProductData['variant_id'])
                ->where('product_id', $newProductData['product_id'])
                ->update([
                    'price' => $newProductData['price']
                ]);
    
            return true;
        } else {
            return false;
        }
    }
}