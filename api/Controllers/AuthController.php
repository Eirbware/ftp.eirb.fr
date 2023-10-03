<?php

namespace FtpEirb\Controllers;

use FtpEirb\Models\TempAccess;
use FtpEirb\Models\User;
use FtpEirb\Models\UserSite;

class AuthController
{
    // This property is set by the AuthMiddleware
    /** @var \FtpEirb\Models\User */
    public static $user;

    public static function redirect(): bool
    {
        $authUrl = "https://cas.bordeaux-inp.fr/login?service=https://ftp.eirb.fr";
        response()->redirect($authUrl);
        return true;
    }

    public static function cas(): bool
    {
        $token = input("token", null);
        $ticket = input("ticket", null);

        // One of the required parameters is missing
        if ($token === null || $ticket === null || !is_string($token) || !is_string($ticket)) {
            return error("Le paramètre 'token' ou 'ticket' est manquant !", "MISSING_PARAMETER", 401);
        }

        // Split domain and redirectUrl
        $parts = explode("@", $token);
        $redirectUrl = base64_decode($parts[0]);
        $callbackUrl = $redirectUrl . "?token=$token&ticket=$ticket";
        response()->redirect($callbackUrl);
        return true;
    }

    public static function success(): void
    {
        echo 'Authentification en cours... Veuillez patienter.';
        exit(0);
    }

    private static function loginAs(string $username): bool
    {
        // We check if the user exists in the database
        $user = User::getById($username);

        if ($user !== null) {
            $userData = [
                "id" => $user->id,
                "first_name" => $user->first_name,
                "last_name" => $user->last_name,
                "admin" => $user->admin ? true : false,
                "added_by" => $user->added_by
            ];

            // We store the user's login in the session
            $_SESSION['user'] = $userData;

            return success("Utilisateur connecté avec succès !", "AUTH/USER_AUTHENTICATED", $userData);
        } else {
            return error("Utilisateur non autorisé à utiliser l'application !", "USER_NOT_AUTHORIZED", 401);
        }
    }

    public static function verify(): bool
    {
        $ticket = input("ticket", null);

        // One of the required parameters is missing
        if ($ticket === null) {
            return error("Le paramètre 'ticket' est manquant !", "MISSING_PARAMETER", 401);
        }

        // We validate the CAS ticket
        $casServiceUrl = "https://ftp.eirb.fr";
        $casValidationUrl = "https://cas.bordeaux-inp.fr/serviceValidate?service=" . urlencode($casServiceUrl) . "&ticket=" . $ticket;

        // create curl resource
        $ch = curl_init();
        // set url
        curl_setopt($ch, CURLOPT_URL, $casValidationUrl);
        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // $output contains the output string
        $casValidationResult = curl_exec($ch);
        // close curl resource to free up system resources
        curl_close($ch);

        if (!is_string($casValidationResult)) {
            return error("La validation du ticket CAS a échoué !", "VALIDATION_ERROR", 401);
        }

        // We check if the validation was successful
        if (strpos($casValidationResult, '<cas:authenticationSuccess>') === false) {
            return error("Le ticket CAS est invalide !", "INVALID_TICKET", 401);
        }

        // We extract the user's login from the CAS response
        $username = explode('<cas:user>', $casValidationResult)[1];
        $username = explode('</cas:user>', $username)[0];

        return self::loginAs($username);
    }

    public static function logout(): bool
    {
        // We delete all active accesses
        $userSites = UserSite::getAllByFields(["user_id" => $_SESSION['user']['id']]);
        TempAccess::deleteAllByFieldIn(
            "access_id",
            array_map(function ($userSite) {
                return $userSite->id;
            }, $userSites)
        );

        // We remove the user's login from the session
        unset($_SESSION['user']);

        // We return a success message
        return success("Utilisateur déconnecté avec succès !", "AUTH/USER_LOGGED_OUT");
    }

    public static function user(): bool
    {
        // We return the user's login details
        return success("Utilisateur déjà connecté !", "AUTH/USER_ALREADY_LOGGED_IN", AuthController::$user);
    }
}
