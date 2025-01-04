<?php

namespace App\Models;

use CodeIgniter\Model;

class carts extends Model
{
    //constructor function
    public function __construct() {
        // Initialize database connection and assign the query builder to the users table
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('cart');
        $this->product_variants_builder = $this->db->table('product_variants');
    }

    //get all items from cart table under that are equal to user_id
    public function getAllByUserId($user_id, &$data = []) {
        //get every coffee, with weight = 250gram and is pricing from the product_variants table
        $coffeeResults = $this->builder
                              ->select('cart.product_id, cart.variant_id, cart.quantity, products.name, products.image_url, product_variants.weight, product_variants.price')
                              ->join('products', 'cart.product_id = products.id') // join product -> id to get from product table
                              ->join('product_variants', 'cart.variant_id = product_variants.id') // join product -> id to get weight from variants
                              ->where('cart.user_id', $user_id)
                              ->get(); //getall no where
        
        // Check if there are results and return them
        if ($coffeeResults->getNumRows() > 0) {
            $data = $coffeeResults->getResultArray();
            
            //calculate the totalprice for every item in cart
            foreach ($data as &$item) {
                // Calculate the total price (quantity * price)
                $item['price'] = $item['quantity'] * $item['price'];
            }

            return true;
        } else {
            return false;
        }
    }

    //update quantity when item is already in cart
    public function updateQuantityWhenInCart($cart_data) {
        $result = $this->builder
                        ->select('quantity')
                        ->where('user_id', $cart_data['user_id'])
                        ->where('product_id', $cart_data['product_id'])
                        ->where('variant_id', $cart_data['variant_id'])
                        ->get()
                        ->getRow(); //get a single row of results
    
        //check if the product exists in the cart
        if ($result) { //yes, item exists in db
            //update the quantity by adding the new quantity to the existing one
            $new_quantity = $result->quantity + $cart_data['quantity'];
    
            // Now update the quantity in the cart
            $update_result = $this->builder
                                   ->set('quantity', $new_quantity)
                                   ->where('user_id', $cart_data['user_id'])
                                   ->where('product_id', $cart_data['product_id'])
                                   ->where('variant_id', $cart_data['variant_id'])
                                   ->update();
    
            //return true if update was successful
            return $update_result ? true : false;
        } else { //no, item does not exist in the cart
            return false;
        }
    }

    //insert product into cart function
    public function insertProductToCart($cart_data) {
        // Initialize database connection and assign the query builder to the users table
        $db = \Config\Database::connect();
        $builder = $db->table('cart'); //connect to cart table
        // Attempt to insert the new user data into the 'users' table
        $insertProductToCart = $builder->insert($cart_data);
        
        // Check if the insertion was successful
        if ($insertProductToCart) {
            return true; // Successfully inserted
        } else {
            return false; // Insertion failed
        }
    }

    //get row count function
    public function getRowCount(&$data = []) {
        //getall from cart table
        $coffeeResults = $this->builder
                                    ->where('user_id', $data['user_id'])
                                    ->get();

        //return number of rows into $data
        $data = $coffeeResults->getNumRows();

        //always return true even when 0 -> so there would be 0 products in cart
        return true;
    }

    //delete product function
    public function deleteItem($data) {
        //apply where conditions and execute the delete operation
        $this->builder
                    ->where('user_id', $data['user_id'])
                    ->where('product_id', $data['product_id'])
                    ->where('variant_id', $data['variant_id'])
                    ->delete();
        
        //check if any rows were affected (deleted)
        if ($this->db->affectedRows() > 0) {
            return true; //deletion was successful
        } else {
            return false; //no rows were deleted (product not found or other issue)
        }
    }

    //updare products quantity
    public function updateProductQuantity($cart_data) {
        $result = $this->builder
                        ->select('quantity')
                        ->where('user_id', $cart_data['user_id'])
                        ->where('product_id', $cart_data['product_id'])
                        ->where('variant_id', $cart_data['variant_id'])
                        ->get()
                        ->getRow(); //get a single row of results
        
        // var_dump($cart_data['event']);die;
        //check if the product exists in the cart
        if ($result) { //yes, item exists in db
            if ($cart_data['event'] == 'increment') {
                $new_quantity = $result->quantity + 1;
            } else if($cart_data['event'] == 'decrement') {
                if($result->quantity == 1) {
                    return false;
                } else {
                    $new_quantity = $result->quantity - 1;
                }
            }
            // Now update the quantity in the cart
            $update_result = $this->builder
                            ->set('quantity', $new_quantity)
                            ->where('user_id', $cart_data['user_id'])
                            ->where('product_id', $cart_data['product_id'])
                            ->where('variant_id', $cart_data['variant_id'])
                            ->update();

            //return true if update was successful
            return $update_result ? true : false;
        } else { //no, item does not exist in the cart
        return false;
        }
    }

    public function calculateTotalPrice($currentUser, &$data) {
        $cartResults = $this->builder
                            ->where('user_id', $currentUser)
                            ->get();
        
        //check if there are cart items
        if ($cartResults->getNumRows() > 0) {
            $cart_data = $cartResults->getResultArray();
            $totalPrice = 0;
    
            //loop through each cart item
            foreach ($cart_data as $cart_item) {
                //get the price for the specific product variant
                $cartResults = $this->product_variants_builder
                                    ->select('price')
                                    ->where('id', $cart_item["variant_id"])
                                    ->where('product_id', $cart_item["product_id"])
                                    ->get();
    
                //check if the result is not empty
                $product = $cartResults->getRow();
                if ($product) {
                    //get the price from the result row
                    $price_single_product = $product->price;
                    //calculate the total price for this product variant
                    $total_price_single_product = $price_single_product * $cart_item["quantity"];
                    //add to the total price
                    $totalPrice += $total_price_single_product;
                } else {
                    return false; //no product price found
                }
            }
    
            //return the total price for the cart
            $data = $totalPrice;
            return true;
        } else {
            return false; //no cart items found
        }
    }
}