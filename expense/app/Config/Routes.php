<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'DashboardController::index');

$routes->post('chat/send',    'ChatController::send');
$routes->delete('chat/clear', 'ChatController::clear');
