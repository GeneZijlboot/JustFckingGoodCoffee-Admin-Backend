<?php
namespace App\Controllers;

//define library
use App\Libraries\SendCloud;

class Order extends BaseController
{
    //getting all orders
    public function getAllOrders() {
        //define variables
        $message = null;
        $data = [];

        //getsession
        $session = session();
        $currentUser = $session->get('currentUser');

        if ($status = (isset($currentUser) && $currentUser["user_role_id"] == 1)) {
            if ($status = $this->cartsModel->getAllByUserId($currentUser["id"], $card)) {
                $data['provider'] = 'SendCloud';
                if ($status = $this->apiModel->getSendCloudKeys($data)) {
                     //get all orders from sendcloud
                    if ($status = $this->SendCloud->getOrders($apiKey, $data, $message)) {
                        $message = 'succesfully.got.orders.from.Lieke';
                    } else {
                        $message = 'something.went.wrong.getting.orders.from.lieke';
                    }
                }else {
                    $message = 'unable.to.get.sencloud.keys';
                }
            } else {
    
            }
        } else {
            $message = 'not.correct.rights';
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
}