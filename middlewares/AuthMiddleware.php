<?php

use Pecee\Http\Middleware\IMiddleware;
use Pecee\Http\Request;

class AuthMiddleware implements IMiddleware {

    public function handle(Request $request): void 
    {
        if (!isset($_SESSION['user'])) {
            response()->httpCode(200);
            response()->json([
                'status' => 'error',
                'message' => 'User not logged in',
                'code' => 'AUTH/USER_NOT_LOGGED_IN'
            ]);
        } else {
            $request->user = $_SESSION['user'];
        }
    }
}