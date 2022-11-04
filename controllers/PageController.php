<?php

class PageController
{
    public static function index()
    {
        require_once __DIR__ . '/../views/index.html';
    }

    public static function notFound()
    {
        response()->httpCode(404);
        require_once __DIR__ . '/../views/not-found.html';
    }
}
