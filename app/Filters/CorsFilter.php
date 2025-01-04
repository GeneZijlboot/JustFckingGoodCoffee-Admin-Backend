<?php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Config\Cors;

class CorsFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $config = new Cors();

        $response = service('response');
        $response->setHeader('Access-Control-Allow-Origin', implode(', ', $config->default['allowedOrigins']));
        $response->setHeader('Access-Control-Allow-Methods', implode(', ', $config->default['allowedMethods']));
        $response->setHeader('Access-Control-Allow-Headers', implode(', ', $config->default['allowedHeaders']));
        $response->setHeader('Access-Control-Allow-Credentials', $config->default['supportsCredentials'] ? 'true' : 'false');

        // Handle OPTIONS request
        if ($request->getMethod() === 'options') {
            $response->setStatusCode(200);
            return $response;
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Optionally, you can perform actions after the request is handled
    }
}
