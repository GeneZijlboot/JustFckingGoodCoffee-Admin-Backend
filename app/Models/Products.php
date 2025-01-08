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
}