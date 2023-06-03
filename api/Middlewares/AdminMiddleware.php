<?php

namespace FtpEirb\Middlewares;

use Pecee\Http\Middleware\IMiddleware;
use Pecee\Http\Request;

class AdminMiddleware implements IMiddleware
{

    public function handle(Request $request): void
    {
        if ($_SESSION['user']['admin'] !== true) {
            error("User not authorized", "AUTH/USER_NOT_AUTHORIZED", 403);
        }
    }
}
