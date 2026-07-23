<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

$routes->group('api', function($routes) {
    // Auth routes
    $routes->post('login', 'AuthController::login');
    $routes->post('register', 'AuthController::register');

    // Public catalog routes
    $routes->get('products', 'ProductController::index');
    $routes->get('products/(:num)', 'ProductController::show/$1');
    $routes->get('products/(:num)/reviews', 'ProductController::reviews/$1');
    $routes->get('categories', 'ProductController::categories');

    // Protected client routes (JWT auth)
    $routes->group('', ['filter' => 'jwt'], function($routes) {
        // Cart
        $routes->get('cart', 'CartController::index');
        $routes->post('cart', 'CartController::add');
        $routes->put('cart/(:num)', 'CartController::update/$1');
        $routes->delete('cart/(:num)', 'CartController::delete/$1');

        // Address
        $routes->get('address', 'AddressController::index');
        $routes->post('address', 'AddressController::create');
        $routes->put('address/(:num)/set-utama', 'AddressController::setUtama/$1');
        $routes->delete('address/(:num)', 'AddressController::delete/$1');

        // Profile management
        $routes->put('profile', 'AuthController::updateProfile');
        $routes->put('profile/password', 'AuthController::updatePassword');
        $routes->put('profile/homepage', 'AuthController::updateHomepage');

        // Orders
        $routes->get('orders', 'OrderController::index');
        $routes->get('orders/(:num)', 'OrderController::show/$1');
        $routes->post('orders', 'OrderController::create');
        $routes->post('orders/(:num)/konfirmasi-tiba', 'OrderController::konfirmasiTiba/$1');

        // Payment
        $routes->post('payment', 'PaymentController::create');

        // Chat
        $routes->get('chat', 'ChatController::index');
        $routes->post('chat', 'ChatController::send');
        $routes->put('chat/(:num)', 'ChatController::edit/$1');
        $routes->delete('chat/(:num)', 'ChatController::delete/$1');

        // Notifications
        $routes->get('notifications', 'NotificationController::index');
        $routes->put('notifications/(:num)/read', 'NotificationController::markAsRead/$1');
        $routes->put('notifications/read-all', 'NotificationController::markAllAsRead');

        // Admin Specific routes (Role check in controller)
        $routes->get('admin/dashboard', 'AdminController::dashboard');
        $routes->get('admin/products', 'AdminController::products');
        $routes->post('admin/products', 'AdminController::createProduct');
        $routes->put('admin/products/(:num)', 'AdminController::updateProduct/$1');
        $routes->delete('admin/products/(:num)', 'AdminController::deleteProduct/$1');

        $routes->get('admin/categories', 'AdminController::categories');
        $routes->post('admin/categories', 'AdminController::createCategory');
        $routes->put('admin/categories/(:num)', 'AdminController::updateCategory/$1');
        $routes->delete('admin/categories/(:num)', 'AdminController::deleteCategory/$1');

        $routes->get('admin/brands', 'AdminController::brands');
        $routes->post('admin/brands', 'AdminController::createBrand');
        $routes->put('admin/brands/(:num)', 'AdminController::updateBrand/$1');
        $routes->delete('admin/brands/(:num)', 'AdminController::deleteBrand/$1');

        $routes->get('admin/customers', 'AdminController::customers');
        $routes->put('admin/customers/(:num)/status', 'AdminController::updateCustomerStatus/$1');
        $routes->delete('admin/customers/(:num)', 'AdminController::deleteCustomer/$1');
        $routes->get('admin/payment', 'AdminController::payments');
        $routes->put('admin/payment/(:num)', 'AdminController::verifyPayment/$1');
        $routes->get('admin/shipping', 'AdminController::shippingList');
        $routes->post('admin/shipping', 'AdminController::createShipping');
        $routes->get('admin/orders', 'AdminController::orders');
        $routes->put('admin/orders/(:num)', 'AdminController::updateOrder/$1');
        $routes->get('admin/reports', 'AdminController::reports');
        $routes->get('admin/reviews', 'AdminController::reviews');
        $routes->post('admin/reviews/(:num)/reply', 'AdminController::replyReview/$1');

        // Admin Notifications (alias)
        $routes->get('admin/notifications', 'NotificationController::index');

        // Admin Chat routes
        $routes->get('admin/chat/users', 'ChatController::adminUserList');
        $routes->get('admin/chat', 'ChatController::index');
        $routes->post('admin/chat', 'ChatController::send');
        $routes->put('admin/chat/(:num)', 'ChatController::edit/$1');
        $routes->delete('admin/chat/(:num)', 'ChatController::delete/$1');
    });
});
