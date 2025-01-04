<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */


// Define backend API routes here (if any)
// Example:
// $routes->get('api/users', 'Api\UsersController::index');
$routes->get('/page', 'page::index');