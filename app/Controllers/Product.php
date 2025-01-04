<?php

namespace App\Controllers;
 
//define models
use App\Models\Products;

class Product extends BaseController
{
    //constructor function
    public function __construct() {
        //products Model
        $this->productsModel = model(Products::class);
    }

    //function to get product table:

    //function to edit row from product table:

    //function to remove item from product table:
}