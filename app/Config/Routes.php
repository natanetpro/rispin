<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Auth Routes
$routes->get('/login', 'AuthController::login');
$routes->post('/login', 'AuthController::attemptLogin');
$routes->get('/logout', 'AuthController::logout');
$routes->get('/register', 'AuthController::register');
$routes->post('/register', 'AuthController::attemptRegister');
$routes->get('/dashboard', 'Home::dashboard', ['filter' => 'auth']);

// Konfigurasi Routes
$routes->group('konfigurasi', ['filter' => 'auth'], function($routes) {
    // Role Routes
    $routes->match(['get', 'post'], 'role', 'Konfigurasi\RoleController::index');
    $routes->get('role/show/(:num)', 'Konfigurasi\RoleController::show/$1');
    $routes->post('role/create', 'Konfigurasi\RoleController::create');
    $routes->post('role/update/(:num)', 'Konfigurasi\RoleController::update/$1');
    $routes->delete('role/delete/(:num)', 'Konfigurasi\RoleController::delete/$1');
});
