<?php

namespace App\Libraries;

class Mollie {

    public function __construct()
    {
        //setting the apiUrl
        $this->apiUrl = "https://api.mollie.com";
    }

    //Mollie request function
    public function performMollieRequest (&$data = [], &$message = null) {
        //parameters
        $status = true;

        //check uri ( endpoints )
        if (!$status = isset($data['uri']) && is_string($data['uri']) && strlen($data['uri'])) {
            $message = 'message.mollie_request.uri.not-found';
            return $status;
        }

        //create the full url
        $fullurl = $this->apiUrl . $data['uri'];

        //initialize cURl session
        $ch = curl_init($fullurl);

        //set headers
        $headers = [
            //setting the apikey
            'Authorization: Bearer ' . $data['apikey'],
            'Content-Type: application/json'
        ];

        //set options for authentication
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        //set options for chosen method
        switch(strtoupper($data['method'])) {
            case 'GET':
                break;
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data['params']));
                break;
            default:
                throw new Exception('Unsupported HTTP method.');
        }

        //execute cURL
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $status = false;
        }

        //set variables
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        $headerSent = curl_getinfo($ch, CURLINFO_HEADER_OUT);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        //debug options:
        // print_r ($header_size);
        // print_r ($header);
        // print_r ($body);
        // print_r ($headerSent);
        // print_r ($httpCode);
        
        //close off the request
        curl_close($ch);

        //security checks
        if (isset($body) && is_string($body) && strlen($body) && $body === 'true') {
            $status = true;
            $data = [];
        } else if (isset($body) && is_string($body) && strlen($body) && $body === 'false') {
            $status = false;
            $data = [];
        } else if (isset($body) && is_string($body) && strlen($body) && 'check if body is json encoded') {
            $data = json_decode($body, true);

            if (isset($data['trace']) && is_string($data['trace']) && strlen($data['trace'])) {
                $status = false;
                $data = $data['message'];
            }
        }

        return $status;
    }

    public function cartPayment($apiKey, &$data = [], &$message = null) {
        $data = [
            'method' => 'POST',
            'uri' => '/v2/payments',
            'apikey' => $apiKey,
            'params' => $data //json array to create the payment with the address, amount etc.
        ];

        return $this->performMollieRequest($data, $message);
    }
}