<?php

namespace FtpEirb;

require_once __DIR__ . '/../vendor/autoload.php';

use Exception;
use Pecee\SimpleRouter\SimpleRouter as Router;
use FtpEirb\Middlewares\AuthMiddleware;
use FtpEirb\Middlewares\AdminMiddleware;

// We start the session
session_start();

Router::get('/index.php', 'PageController@index');
Router::get('/', 'PageController@index');

Router::group(['prefix' => '/api'], function () {
    // Authentication routes (not already authenticated)
    Router::group(['prefix' => '/auth'], function () {
        Router::get('/redirect', 'AuthController@redirect');
        Router::get('/cas', 'AuthController@cas');
        Router::get('/success', 'AuthController@success');
        Router::get('/verify', 'AuthController@verify');
    });

    // Authenticated routes
    Router::group(['middleware' => AuthMiddleware::class], function () {
        Router::get('/auth/logout', 'AuthController@logout');
        Router::get('/auth/user', 'AuthController@user');

        Router::get('/sites', 'DashboardController@listSites');
        Router::get('/accesses', 'DashboardController@listAccesses');
        Router::post('/accesses', 'DashboardController@createAccess');
        Router::delete('/accesses/{id}', 'DashboardController@deleteAccess');
        Router::get('/all', 'DashboardController@listAll');

        // Admin routes
        Router::group(['middleware' => AdminMiddleware::class, 'prefix' => '/admin'], function () {
            Router::get('/', 'AdminController@listAll');

            Router::get('/users', 'AdminController@listUsers');
            Router::post('/users', 'AdminController@createUser');
            Router::put('/users/{id}', 'AdminController@updateUser');
            Router::delete('/users/{id}', 'AdminController@deleteUser');

            Router::get('/sites', 'AdminController@listSites');
            Router::post('/sites', 'AdminController@createSite');
            Router::put('/sites/{id}', 'AdminController@updateSite');
            Router::delete('/sites/{id}', 'AdminController@deleteSite');

            Router::get('/accesses', 'AdminController@listAccesses');
            Router::post('/accesses', 'AdminController@createAccess');
            Router::delete('/accesses/{id}', 'AdminController@deleteAccess');
        });
    });
});

Router::error(function ($request, Exception $exception) {
    switch ($exception->getCode()) {
        case 404: // Page not found
            $request->setRewriteCallback('PageController@notFound');
            break;
        case 403: // Forbidden
            $request->setRewriteCallback('PageController@forbidden');
            break;
        case 401: // Unauthorized
            $request->setRewriteCallback('PageController@unauthorized');
            break;
        default: // Internal server error
            logError($exception->getMessage());
            $request->setRewriteCallback('PageController@internalServerError');
            break;
    }
});

// Start the routing
Router::setDefaultNamespace('\FtpEirb\Controllers');
Router::start();

