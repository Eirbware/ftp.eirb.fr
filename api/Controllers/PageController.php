<?php

namespace FtpEirb\Controllers;

class PageController
{
    public static function index(): void
    {
        require_once __DIR__ . '/../Views/index.html';
    }

    public static function notFound(): void
    {
        error("Ressource non trouvée !", "NOT_FOUND", 404);
    }

    public static function forbidden(): void
    {
        error("Vous n'êtes pas autorisé à accéder à cette ressource !", "FORBIDDEN", 403);
    }

    public static function unauthorized(): void
    {
        error("Vous n'êtes pas autorisé à accéder à cette ressource !", "UNAUTHORIZED", 401);
    }

    public static function internalServerError(): void
    {
        error("Une erreur interne est survenue !", "INTERNAL_SERVER_ERROR", 500);
    }
}
