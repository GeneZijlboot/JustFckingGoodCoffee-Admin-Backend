<?php
namespace App\Controllers;

//define models
use App\Models\Carts;

class Cart extends BaseController
{
    //constructor function
    public function __construct() {
        //users model
        $this->cartsModel = model(Carts::class);
    }

    //function to get cart table:

    //function to edit row from cart table:

    //function to remove item from cart table:
}