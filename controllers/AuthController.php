<?php

class AuthController
{
    public static function redirect()
    {
        $redirectUrl = input()->get("redirectUrl", null);

        // One of the required parameters is missing
        if ($redirectUrl === null) {
            response()->httpCode(401);
            response()->json([
                'status' => 'error',
                'message' => "Le paramètre 'redirectUrl' est manquant !",
                'code' => 'MISSING_PARAMETER'
            ], JSON_PRETTY_PRINT);
        }

        $token = base64_encode($redirectUrl);
        $authUrl = "https://cas.bordeaux-inp.fr/login?service=https://aboin.vvv.enseirb-matmeca.fr/casAuth?token=$token@bordeaux-inp.fr";
        response()->redirect($authUrl);
    }

    public static function cas()
    {
        $token = input()->get("token", null);
        $ticket = input()->get("ticket", null);

        // One of the required parameters is missing
        if ($token === null || $ticket === null) {
            response()->httpCode(401);
            response()->json([
                'status' => 'error',
                'message' => "Le paramètre 'token' ou 'ticket' est manquant !",
                'code' => 'MISSING_PARAMETER'
            ], JSON_PRETTY_PRINT);
        }

        // Split domain and redirectUrl
        $parts = explode("@", $token);
        $redirectUrl = base64_decode($parts[0]);
        $callbackUrl = $redirectUrl . "?token=$token&ticket=$ticket";
        response()->redirect($callbackUrl);
    }

    public static function success()
    {
        echo 'Authentification en cours... Veuillez patienter.';
        exit(0);
    }

    private static function loginAs(string $username)
    {
        // We check if the user exists in the database
        $user = \Models\User::get($username);

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

            response()->json([
                'status' => 'success',
                'message' => "Utilisateur connecté avec succès !",
                'code' => 'AUTH/USER_AUTHENTICATED',
                'data' => $userData
            ], JSON_PRETTY_PRINT);
        } else {
            response()->httpCode(401);
            response()->json([
                'status' => 'error',
                'message' => "Utilisateur non autorisé à utiliser l'application !",
                'code' => 'USER_NOT_AUTHORIZED'
            ], JSON_PRETTY_PRINT);
        }
    }

    public static function verify()
    {
        $token = input()->get("token", null);
        $ticket = input()->get("ticket", null);

        // One of the required parameters is missing
        if ($token === null || $ticket === null) {
            response()->httpCode(401);
            response()->json([
                'status' => 'error',
                'message' => "Le paramètre 'token' ou 'ticket' est manquant !",
                'code' => 'MISSING_PARAMETER'
            ], JSON_PRETTY_PRINT);
        }

        // We validate the CAS ticket
        $casServiceUrl = "https://aboin.vvv.enseirb-matmeca.fr/casAuth?token=" . $token->value;
        $casValidationUrl = "https://cas.bordeaux-inp.fr/serviceValidate?service=" . urlencode($casServiceUrl) . "&ticket=" . $ticket->value;

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

        // We check if the validation was successful
        if (strpos($casValidationResult, '<cas:authenticationSuccess>') === false) {
            response()->httpCode(401);
            response()->json([
                'status' => 'error',
                'message' => "La validation du ticket CAS a échoué !",
                'code' => 'INVALID_TICKET'
            ], JSON_PRETTY_PRINT);
        }

        // We extract the user's login from the CAS response
        $username = explode('<cas:user>', $casValidationResult)[1];
        $username = explode('</cas:user>', $username)[0];

        return self::loginAs($username);
    }

    public static function logout()
    {
        // We delete all active accesses
        \Models\TempAccess::deleteAllForUser($_SESSION['user']['id']);

        // We remove the user's login from the session
        unset($_SESSION['user']);

        // We return a success message
        response()->json([
            'status' => 'success',
            'message' => "Utilisateur déconnecté avec succès !",
            'code' => 'AUTH/USER_LOGGED_OUT'
        ], JSON_PRETTY_PRINT);
    }

    public static function user()
    {
        // We return the user's login details
        response()->json([
            'status' => 'success',
            'message' => "Utilisateur déjà connecté !",
            'code' => 'AUTH/USER_ALREADY_LOGGED_IN',
            'data' => request()->user
        ], JSON_PRETTY_PRINT);
    }

    public static function loginDev()
    {
        $username = input()->get("username", null);

        // One of the required parameters is missing
        if ($username === null) {
            response()->httpCode(401);
            response()->json([
                'status' => 'error',
                'message' => "Le paramètre 'username' est manquant !",
                'code' => 'MISSING_PARAMETER'
            ], JSON_PRETTY_PRINT);
        }

        return self::loginAs($username);
    }
}
