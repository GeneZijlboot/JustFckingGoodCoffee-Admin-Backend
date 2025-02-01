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
        $this->productbuilder = $this->db->table('products');
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

    //get rows based on the given searchValue (with optional search filters)
    public function getBySearchParam(&$searchValue, $searchFields = []) {
        // Ensure the builder is reset or use the appropriate builder
        $this->builder->select('product_variants.id, product_variants.product_id, products.name AS product_name, product_variants.weight, product_variants.price')
                        ->join('products', 'product_variants.product_id = products.id');

        //if a search value is provided, apply the filters dynamically
        if (!empty($searchValue)) {
            $this->builder->groupStart();

            //apply filters for specific fields, or all fields by default
            if (empty($searchFields)) {
                //if no specific search fields are provided, search all relevant fields
                $this->builder->orLike('product_variants.id', $searchValue)
                            ->orLike('product_variants.product_id', $searchValue)
                            ->orLike('product_variants.weight', $searchValue)
                            ->orLike('product_variants.price', $searchValue)
                            ->orLike('products.name', $searchValue);
            } else {
                //if specific fields are provided, apply the search value to them
                foreach ($searchFields as $field) {
                    $this->builder->orLike($field, $searchValue);
                }
            }

            $this->builder->groupEnd();
        }

        //execute the query
        $productVariantResult = $this->builder->get();

        //check if there are results and return them
        if ($productVariantResult->getNumRows() > 0) {
            $searchValue = $productVariantResult->getResultArray();
            return true;
        } else {
            return false;
        }
    }

    //insert given product vairant
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

    //delete by id
    public function DeleteById($productvariant_id) {
        // Check if the user exists
        $productVariantResult = $this->builder()
                            ->where('id', $productvariant_id)
                            ->get();
    
        if ($productVariantResult->getNumRows() > 0) {
            // User exists, proceed to delete
            $this->builder()
                    ->where('id', $productvariant_id)
                    ->delete();
    
            return true; // Successfully deleted
        } else {
            return false; // User not found
        }
    }
}