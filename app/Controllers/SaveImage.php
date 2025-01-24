<?php

namespace App\Controllers;
 
class SaveImage extends BaseController
{
    public function saveProductImage() 
    {
        $message = [];
        $status = true;

        // Define file save directory
        $savePath = ROOTPATH . 'public/images';
        
        // Ensure directory exists
        if (!is_dir($savePath)) {
            mkdir($savePath, 0755, true); // Create directory if it doesn't exist
        }

        // Define parameters
        $data = [
            'productImage' => $this->request->getFile('productImage'),
            'infobarImage' => $this->request->getFile('infobarImage'),
        ];

        foreach ($data as $key => $file) {
            if ($file && $file->isValid() && !$file->hasMoved()) {
                try {
                    // Generate a unique name and move the file
                    $newName = $file->getRandomName();
                    $file->move($savePath, $newName);

                    $message[$key] = "$key uploaded successfully.";
                } catch (\Exception $e) {
                    $status = false;
                    $message[$key] = "Failed to upload $key: " . $e->getMessage();
                }
            } else {
                $status = false;
                $message[$key] = "$key upload failed: " . ($file->getErrorString() ?? 'Unknown error');
            }
        }

        // Define response data
        $response_data = [
            'status' => $status,
            'message' => $message,
        ];

        // Return response in JSON format
        return $this->response->setJSON($response_data);
    }
}