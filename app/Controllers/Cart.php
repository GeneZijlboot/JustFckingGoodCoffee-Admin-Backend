<?php
namespace App\Controllers;

//define models
use App\Models\Carts;
use App\Models\Products;
use App\Models\Users;
use App\Models\ApiKeys;

//load in mollie library
use App\Libraries\Mollie;
use App\Libraries\SendCloud;

class Cart extends BaseController
{
    //constructor function
    public function __construct() {

        //importing librariesssssd
        $this->Mollie = new Mollie();
        $this->SendCloud = new SendCloud();

        //products Model
        $this->productsModel = model(Products::class);

        //users model
        $this->cartsModel = model(Carts::class);

        //users model
        $this->usersModel = model(Users::class);
    }

    //get cart items based on currentuser or cartsession
    public function getCart() {
        //define parameters
        $message = null;
        $data = [];

        //getsession
        $session = session();
        $currentUser = $session->get('currentUser');
        $cartSession = $session->get('cart_data');

        //check if user is logged in
        if ($status = isset($currentUser)) { //user is logged in
            if ($status = $this->cartsModel->getAllByUserId($currentUser["id"], $data)) {
                $message = 'getting.cart.succesful';
            } else {
                $message = 'getting.cart.failed';
            }
        } else if ($status = isset($cartSession)) { //user is not logged in -> get session cart
            //set parameters
            $data = $cartSession;
            $message = 'getting.session.cart.succesful';
        } else {
            $message = 'no.prodcuts.in.cart.session';
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

    //adding clicked on product to cart or session
    public function addToCart () {
        //define parameters
        $message = null;
        $data = [
            'product_id' => $this->request->getPostGet('product_id'),
            'variant_id' => $this->request->getPostGet('variant_id'),
            'quantity' => $this->request->getPostGet('quantity'),
        ];

        //getsession
        $session = session();
        $currentUser = $session->get('currentUser');
        $cartSession = $session->get('cart_data');

        //check if user is logged in:
        if (isset($currentUser)) {
            //add user_id to $cart_data
            $data['user_id'] = $currentUser["id"];
            
            //check if item already exist in database
            if ($status = $this->cartsModel->updateQuantityWhenInCart($data)) { //item already exists in db, so update its quantity
                $message = 'succesfully.added.product.to.cart';
            } else { 
                if ($status = $this->cartsModel->insertProductToCart($data)) { //item does not exist in db, so insert it in db
                    $message = 'succesfully.added.product.to.cart';
                }
            }
        } else {
            //get cart_session, if not initialized make empty array
            $cartSession = $session->get('cart_data') ?? [];

            //get the correct data for the product with a join
            if ($status = $this->productsModel->getProductDetails($data)) {
                //check if product already in session cart
                $found = false;

                //if product is already in session than just update its quantity
                foreach ($cartSession as &$sessionItem) {
                    if ($sessionItem['product_id'] === $data['product_id'] && $sessionItem['variant_id'] === $data['variant_id']) {
                        //update quantity if product already exists
                        $sessionItem['quantity'] += $data['quantity'];

                        $sessionItem['price'] = $sessionItem['price'] * $data['quantity'];

                        $found = true;
                        $message = 'successfully.updated.product.quantity.in.session';
                        break;
                    }
                }
    
                //if product is new to session cart, add it
                if (!$found) {
                    $data['price'] = $data['price'] * $data['quantity'];
                    $cartSession[] = $data;
                    $message = 'successfully.added.product.to.session.cart';
                }
                    
                //update session with modified cart data
                $session->set('cart_data', $cartSession);
                $data = $cartSession;
                $status = true;
            } else {
                $message = 'couldnt.get.product.details';
            }
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

    //updating a products quantity
    public function updateProductQuantity() {
        //define parameters
        $message = null;
        $data = [
            'product_id' => $this->request->getPostGet('product_id'),
            'variant_id' => $this->request->getPostGet('variant_id'),
            'event' => $this->request->getPostGet('event'),
        ];

        //getsession
        $session = session();
        $currentUser = $session->get('currentUser');
        $cartSession = $session->get('cart_data');

        //check if user is logged in:
        if (isset($currentUser)) {
            //add user_id to $cart_data
            $data['user_id'] = $currentUser["id"];

            if ($status = $this->cartsModel->updateProductQuantity($data)) { //update product quantity
                $message = 'succesfully.updated.quantity';
            }
        } else {
            foreach ($cartSession as $key => $item) {
                // Check if product_id and variant_id match
                if ($item['product_id'] == $data['product_id'] && $item['variant_id'] == $data['variant_id']) {
                    if ($data['event'] == 'increment') {
                        // Increment quantity
                        $newquantity = $item['quantity'] + 1;

                        //calculate new price based on new quantity
                        $price_of_one_product = $item['price'] / $item['quantity'];
                        $newprice = $price_of_one_product * $newquantity;

                        // Output to confirm inside the loop
                        $status = true;
                        $message = 'incremented.quantity';
                    } else if ($data['event'] == 'decrement') {
                        if ($item['quantity'] > 1) {
                            // Decrement quantity
                            $newquantity = $item['quantity'] - 1;

                            // Output to confirm inside the loop
                            $status = true;
                            $message = 'decremented.quantity';
                        } else {
                            $status = false;
                            $message = 'cant.decrement.one.item';
                        }
                    }
                    //update values to new values
                    $price_of_one_product = $item['price'] / $item['quantity'];
                    $newprice = round($price_of_one_product * $newquantity, 2);
    
                    //update values to new values
                    $cartSession[$key]['quantity'] = $newquantity;
                    $cartSession[$key]['price'] = $newprice;
                }
            }
            //output to confirm outside the loop
            $session->set('cart_data', $cartSession);
            $data = $cartSession;
        }

        ///define response data
        $response_data = [
            'status' => $status,
            'data' => $data,
            'message' => $message
        ];

        //return response back to frontend -> in JSON format
        return $this->response->setJSON($response_data);
    }

    //delete cart items based on currentuser or cartsession
    public function deleteProduct() {
        //define parameters
        $message = null;
        $data = [
            'product_id' => $this->request->getPostGet('product_id'),
            'variant_id' => $this->request->getPostGet('variant_id'),
        ];
    
        //get session
        $session = session();
        $currentUser = $session->get('currentUser');
        $cartSession = $session->get('cart_data');
    
        //check if currentUser is set
        if (isset($currentUser)) {
            //define currentUser's user_id
            $data['user_id'] = $currentUser["id"];
                    
            //delete product with given parameters  
            if ($status = $this->cartsModel->deleteItem($data)) {
                $message = "product.succesfully.deleted";
            } else {
                $message = "failed.to.delete.product";
            }
        } else {
            //remove product with given product_id and variant_id
            $productKey = $data['product_id'];
            $variantKey = $data['variant_id'];
        
            //loop through the cart session
            foreach ($cartSession as $key => $sessionItem) {
                //check if session item has product_id and variant_id
                if (isset($sessionItem['product_id']) && isset($sessionItem['variant_id'])) {
                    //check if product_id and variant_id match
                    if ($sessionItem['product_id'] == $productKey && $sessionItem['variant_id'] == $variantKey) {
                        //remove item from cart session array
                        unset($cartSession[$key]); //correctly remove the item from the array
                        
                        //save the updated cart session
                        $session->set('cart_data', $cartSession);
                        
                        //indicate success
                        $status = true;
                        $message = "product.successfully.removed.from.cart";
                        break; //exit the loop once the item is found and removed
                    } else {
                        //this message can be skipped since you are already inside the loop checking for matches
                        $status = false;
                        $message = 'failed.to.remove.item';
                    }
                } else {
                    $status = false;
                    $message = 'product.not.found.in.cart';
                }
            }
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

    //get row count ( to display the amount of products in cart )
    public function productCount() {
        //define parameters
        $message = null;
        $data = [];

        //getsession
        $session = session();
        $currentUser = $session->get('currentUser');
        $cartSession = $session->get('cart_data');
 
        //check if currentUser is set
        if (isset($currentUser)) {
            //define currentUsers user_id
            $data['user_id'] = $currentUser["id"];
            if ($status = $this->cartsModel->getRowCount($data)) {
                $message = 'succesfully.counted.rows';
            } else {
                $message = 'unable.to.get.rowcount';
            }
        } else {
            if ($status = $cartSession) {
                //give back the number of items in an array
                $data = count($cartSession);
            }
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

    //function to get total price of all products in cart
    public function getTotalPrice() {
        //define parameters
        $message = null;
        $data = [];

        //getsession
        $session = session();
        $currentUser = $session->get('currentUser');
        $cartSession = $session->get('cart_data');

        //check if currentUser is set
        if (isset($currentUser)) { // logged in so get totalprice of the logged in user
            if ($status = $this->cartsModel->calculateTotalPrice($currentUser["id"], $data)) {
                $message = 'succesfully.calculated.total.price';
            } else {
                $message = 'failed.to.get.total.price';
            }
        } else {
            if ($status = isset($cartSession)) {
                $totalPrice = 0;
                
                //loop through cartSession
                foreach ($cartSession as $sessionItem) {
                    //add the price of every product in cartSession array to totalprice
                    $totalPrice = $totalPrice + $sessionItem["price"];
                }
                //store total_price
                $data = $totalPrice;

                //check if it succesfully stored it
                if($status = isset($data)) {
                    $message = 'succesfully.calculated.totalprice';
                } else {
                    $message = 'error.calculating.price';
                }
            } else {
                $message = 'cart.is.empty';
            }
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

    //checkout function
    public function checkout() {
        // //define model
        // $cartModel = model(Carts::class);
        // $usersModel = model(Users::class);
        // $apiModel = model(ApiKeys::class);

        // //define parameters
        // $message = null;
        // $totalPrice = 0;
        // $data = [];

        // //get currensession
        // $session = session();
        // $currentUser = $session->get('currentUser');

        // //check if currentUser is logged in
        // if (isset($currentUser)) {
        //     //check if the currentUsers billing adress is filled in
        //     if ($status = $usersModel->checkCurrentUsersBillingAddress($currentUser["id"])) { //all information is filled in!
        //         //where on mollie
        //         $data['provider'] = 'Mollie';

        //         //succesfully got mollie api key 
        //         if ($status = $apiModel->getMollieKeys($data)) {
        //             $apiKey = $data["api_key"];
                    
        //             //pak de met de user id alle rows in cart op basis van het user id, vervolgens bereken je per product (loop) met de quantity en het product_variant wat de kosten zijn, dan de volgende dan weer etc.
        //             if ($status = $cartModel->getAllByUserId($currentUser["id"], $totalCardValue)) {
        //                 foreach($totalCardValue as $item) {
        //                     $totalPrice += $item["price"];
        //                     $totalPrice = round($totalPrice, 2);
        //                 }

        //                 // array insert payment for mollie
        //                 $createPayment = [
        //                     // 'mode' => 'live',
        //                     'amount' => [
        //                         'value' => number_format($totalPrice, 2, '.', ''),
        //                         'currency' => 'EUR',
        //                     ],
        //                     'redirectUrl' => 'http://localhost:5173/succes_payment', // Replace with a valid, accessible URL.
        //                     'description' => 'test description',
        //                 ];

        //                 //make the cart payment with mollie
        //                 if ($status = $this->Mollie->cartPayment($apiKey, $createPayment, $message)) {
        //                     if ($status = isset($createPayment["_links"]["checkout"]["href"])) {
        //                         $data['redirectUrl'] = $createPayment["_links"]["checkout"]["href"];
        //                         $message = 'succesfully.created.payment';

        //                         $data['provider'] = 'SendCloud';
        //                         //succesfully got mollie api key
        //                         if ($status = $apiModel->getSendCloudKeys($data)) {
        //                             //succesfully got the sendcloud keys
        //                             $apiKey = $data["api_key"];
        //                             var_dump($apiKey);
        //                             var_dump($currentUser);die;

        //                             //currentuser order voor Lieke ( SendCloud )
        //                             $createOrder = [

        //                             ];
        //                             if ($status = $this->SendCloud->createOrder($apiKey, $data, $message)) {
                                        
        //                             }
        //                         } else {
                                    
        //                         }
        //                     } else {
        //                         $message = 'Unable to get checkout URL from Mollie';
        //                     }
        //                 }
        //             }
        //          } else {
        //             $message = 'unable.to.fetch.mollie.api.key';
        //         }
        //     } else {
        //         $message = 'billing.address.is.missing';
        //     }
        // } else { //user not logged in so unable to check out
        //     $status = false;
        //     $message = 'user.not.logged.in';
        // }

        //  //define reponse_data
        //  $response_data = [
        //     'status' => $status,
        //     'data' => $data,
        //     'message' => $message
        // ];

        // //send back data to frontend in json format
        // return $this->response->setJSON($response_data);

        /* livegang data shown: */
        //define parameters
        $status = true;
        $message = 'not.working.yet';
        $data = [];

        //define reponse_data
         $response_data = [
            'status' => $status,
            'data' => $data,
            'message' => $message
        ];

        //send back data to frontend in json format
        return $this->response->setJSON($response_data);
    }
}