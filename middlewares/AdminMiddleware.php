<?php

use Pecee\Http\Middleware\IMiddleware;
use Pecee\Http\Request;

class AdminMiddleware implements IMiddleware {

    public function handle(Request $request): void 
    {
        if ($_SESSION['user']['admin'] !== true) {
            response()->httpCode(403);
            response()->json([
                'status' => 'error',
                'message' => 'User not authorized',
                'code' => 'AUTH/USER_NOT_AUTHORIZED'
            ]);
        }
    }
}