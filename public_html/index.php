<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Pecee\SimpleRouter\SimpleRouter;

// We start the session
session_start();

// We load the project dependencies
require_once __DIR__ . '/../services/Helpers.php';
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Authentication.php';

require_once __DIR__ . '/../middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../middlewares/AdminMiddleware.php';

require_once __DIR__ . '/../controllers/PageController.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/AdminController.php';
require_once __DIR__ . '/../controllers/DashboardController.php';

require_once __DIR__ . '/../models/SiteModel.php';
require_once __DIR__ . '/../models/TempAccessModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/UserSiteModel.php';

SimpleRouter::get('/index.php', 'PageController@index');
SimpleRouter::get('/', 'PageController@index');

SimpleRouter::group(['prefix' => '/api'], function () {
    // Authentication routes (not already authenticated)
    SimpleRouter::group(['prefix' => '/auth'], function () {
        SimpleRouter::get('/redirect', 'AuthController@redirect');
        SimpleRouter::get('/cas', 'AuthController@cas');
        SimpleRouter::get('/success', 'AuthController@success');
        SimpleRouter::get('/verify', 'AuthController@verify');
    });

    // Authenticated routes
    SimpleRouter::group(['middleware' => AuthMiddleware::class], function () {
        SimpleRouter::get('/auth/logout', 'AuthController@logout');
        SimpleRouter::get('/auth/user', 'AuthController@user');

        SimpleRouter::get('/sites', 'DashboardController@listSites');
        SimpleRouter::get('/accesses', 'DashboardController@listAccesses');
        SimpleRouter::post('/accesses', 'DashboardController@createAccess');
        SimpleRouter::delete('/accesses/{id}', 'DashboardController@deleteAccess');
        SimpleRouter::get('/all', 'DashboardController@listAll');

        // Admin routes
        SimpleRouter::group(['middleware' => AdminMiddleware::class, 'prefix' => '/admin'], function () {
            SimpleRouter::get('/', 'AdminController@listAll');

            SimpleRouter::get('/users', 'AdminController@listUsers');
            SimpleRouter::post('/users', 'AdminController@createUser');
            SimpleRouter::put('/users/{id}', 'AdminController@updateUser');
            SimpleRouter::delete('/users/{id}', 'AdminController@deleteUser');

            SimpleRouter::get('/sites', 'AdminController@listSites');
            SimpleRouter::post('/sites', 'AdminController@createSite');
            SimpleRouter::put('/sites/{id}', 'AdminController@updateSite');
            SimpleRouter::delete('/sites/{id}', 'AdminController@deleteSite');

            SimpleRouter::get('/accesses', 'AdminController@listAccesses');
            SimpleRouter::post('/accesses', 'AdminController@createAccess');
            SimpleRouter::delete('/accesses/{id}', 'AdminController@deleteAccess');
        });
    });
});

SimpleRouter::error(function ($request, \Exception $exception) {
    if ($exception->getCode() === 404) {
        PageController::notFound();
    }
});

// Start the routing
SimpleRouter::start();
