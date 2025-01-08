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

    //get all users
    public function getAll(&$data = []) {
        $userResult = $this->builder
                                ->select('cart.id, cart.user_id, users.first_name as first_name, users.last_name as last_name, cart.product_id, products.name as product_name, cart.variant_id, cart.quantity')
                                ->join('users', 'cart.user_id = users.id')
                                ->join('products', 'cart.product_id = products.id')
                                ->get();
        //check if there are results and return them
        if ($userResult->getNumRows() > 0) {
            $data = $userResult->getResultArray();
            return true;
        } else {
            return false;
        }
    }
}