<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductVariants extends Model
{
    //constructor fucntion
    public function __construct() {
        // Initialize database connection and assign the query builder to the users table
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('product_variants');
    }

    //get all users
    public function getAll(&$data = []) {
        $productVariantResult = $this->builder
                                        ->select('product_variants.id, product_variants.product_id, products.name AS product_name, product_variants.weight, product_variants.price')
                                        ->join('products', 'product_variants.product_id = products.id')
                                        ->get();
        //check if there are results and return them
        if ($productVariantResult->getNumRows() > 0) {
            $data = $productVariantResult->getResultArray();
            return true;
        } else {
            return false;
        }
    }

    //insert given message
    public function insertProductVariant($data) {
        // Check if a role with the same name already exists
        $existinProductVariant = $this->builder
                                        ->where('product_id', $data['product_id'])
                                        ->where('weight', $data['weight'])
                                        ->get();

        //if a match is found, return false
        if ($existinProductVariant->getNumRows() > 0) {
            return false;
        }

        //insert the new role
        $this->builder->insert($data);

        // Retrieve the newly created role to confirm
        $newProductVariant = $this->builder
                            ->where('product_id', $data['product_id'])
                            ->where('weight', $data['weight'])
                            ->get();

        // If the new role is retrieved successfully, return true
        if ($newProductVariant->getNumRows() > 0) {
            return true;
        }

        // Default return false in case of any issues
        return false;
    }
}