<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->post('api/coasters', 'CoasterController::createCoaster');
$routes->post('api/coasters/(:num)/wagons', 'CoasterController::addWagon/$1');
$routes->delete('api/coasters/(:num)/wagons/(:num)', 'CoasterController::removeWagon/$1/$2');
$routes->put('api/coasters/(:num)', 'CoasterController::updateCoaster/$1');

