<?php

namespace App\Controllers;
 
//define models
use App\Models\Messages;

class Message extends BaseController
{
    //constructor function
    public function __construct() {
        //products Model
        $this->messagesModel = model(Messages::class);
    }

    //get all users
    public function getAll() {
        //define variables
        $message = null;
        $data = [];

        //getsession
        $session = session();
        $currentUser = $session->get('currentUser');

        if ($status = (isset($currentUser) && $currentUser["user_role_id"] == 1)) { // check if a user is logged in and if admin
            if ($status = $this->messagesModel->getAll($data)) {
                foreach ($data as &$translations) { // Use a reference to modify the actual array element
                    $translations['controller'] = 'Message';
                }
                unset($translations); // Unset the reference after the loop to avoid unintended side effects
                //define table headers
                $data['field_headers'] = [
                    '#',
                    'Language',
                    'Name',
                    'Message',
                    '', //for the CRUD icons
                ];
            }
        } else {
            $message = 'not.logged.in';
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

    public function createMessage() {
        //define variables
        $message = null;
        $data = [
            'name' => $this->request->getPostGet('name'),
            'language' => $this->request->getPostGet('language'),
            'message' => $this->request->getPostGet('message'),
        ];

        //getsession
        $session = session();
        $currentUser = $session->get('currentUser');

        if ($status = (isset($currentUser) && $currentUser["user_role_id"] == 1)) { // check if a user is logged in and if admin
            //insert new message
            if ($status = $this->messagesModel->insertMessage($data)) {
                $message = 'succesfully.created.message';
            } else {
                $message = 'message.already.exists';
            }
        } else {
            $message = 'not.logged.in';
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
     
    public function deleteMessage() {
        //define variables
        $message = null;
        $data = [
            'id' => $this->request->getPostGet('id'),
        ];

        //getsession
        $session = session();
        $currentUser = $session->get('currentUser');
    
        if ($status = (isset($currentUser) && $currentUser["user_role_id"] == 1)) { // check if a user is logged in and if admin
            if ($status = $this->messagesModel->DeleteById($data)) {
                $message = 'succesfully.deleted.message';
            }
        } else {
            $message = 'not.logged.in';
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

    //get with language ( translations admin )
    public function getWithLanguage() {
        // Define variables
        $message = null;
        $groupedData = [];
        $status = false;
    
        // Get all translations
        if ($status = $this->messagesModel->getWithLanguage($translations)) {
            $message = 'succesfully.got.messages';
    
            // Process each translation and group by language
            foreach ($translations as $translation) {
                $locale = strtolower($translation['language']); // Ensure locale is lowercase
    
                if (!isset($groupedData[$locale])) {
                    $groupedData[$locale] = []; // Initialize the language block if it doesn't exist
                }
    
                // Add the flat key-value pair for the language
                $groupedData[$locale][$translation['name']] = $translation['message'];
            }
        } else {
            $message = 'no.messages.found';
        }
    
        // Define response data
        $response_data = [
            'status' => $status,
            'data' => $groupedData, // Return grouped data
            'message' => $message
        ];
    
        // Return response back to frontend -> in JSON format
        return $this->response->setJSON($response_data);
    }
}