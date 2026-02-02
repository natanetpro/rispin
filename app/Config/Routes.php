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
$routes->get('/dashboard', 'Home::dashboard', ['filter' => ['auth', 'permission:view_dashboard']]);

// Konfigurasi Routes
$routes->group('konfigurasi', ['filter' => 'auth'], function($routes) {
    
    // Role Manager
    $routes->get('role', 'Konfigurasi\RoleController::index', ['filter' => 'permission:view_role']);
    $routes->get('role/show/(:num)', 'Konfigurasi\RoleController::show/$1', ['filter' => 'permission:view_role']);
    $routes->post('role/create', 'Konfigurasi\RoleController::create', ['filter' => 'permission:create_role']);
    $routes->post('role/update/(:num)', 'Konfigurasi\RoleController::update/$1', ['filter' => 'permission:edit_role']);
    $routes->match(['post', 'delete'], 'role/delete/(:num)', 'Konfigurasi\RoleController::delete/$1', ['filter' => 'permission:delete_role']);
    
    // Permission Assignment
    $routes->get('permissions/(:num)', 'Konfigurasi\RoleController::permissions/$1', ['filter' => 'permission:edit_role']);
    $routes->post('permissions/(:num)', 'Konfigurasi\RoleController::updatePermissions/$1', ['filter' => 'permission:edit_role']);
    
    // Menu Manager
    $routes->get('menu', 'Konfigurasi\MenuController::index', ['filter' => 'permission:view_menu']);
    $routes->get('menu/show/(:num)', 'Konfigurasi\MenuController::show/$1', ['filter' => 'permission:view_menu']);
    $routes->post('menu/create', 'Konfigurasi\MenuController::create', ['filter' => 'permission:create_menu']);
    $routes->post('menu/update/(:num)', 'Konfigurasi\MenuController::update/$1', ['filter' => 'permission:edit_menu']);
    $routes->delete('menu/delete/(:num)', 'Konfigurasi\MenuController::delete/$1', ['filter' => 'permission:delete_menu']);
    $routes->post('menu/saveOrder', 'Konfigurasi\MenuController::saveOrder', ['filter' => 'permission:edit_menu']);
});
