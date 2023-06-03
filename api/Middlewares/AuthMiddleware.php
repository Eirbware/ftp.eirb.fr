<?php

namespace FtpEirb\Middlewares;

use FtpEirb\Controllers\AuthController;
use FtpEirb\Services\Authentication;
use Pecee\Http\Middleware\IMiddleware;
use Pecee\Http\Request;

class AuthMiddleware implements IMiddleware
{

    public function handle(Request $request): void
    {
        $user = Authentication::getCurrentUser();
        if ($user) {
            AuthController::$user = $user;
        } else {
            error("User not logged in", "AUTH/USER_NOT_LOGGED_IN", 200);
        }
    }
}
