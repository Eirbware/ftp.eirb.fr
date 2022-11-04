<?php

class DashboardController
{
    public static function listSites()
    {
        $sites = \Models\Site::getAllForUser(request()->user['id']);

        response()->json([
            'status' => 'success',
            'message' => 'Liste des sites',
            'code' => 'SITE/LIST',
            'data' => $sites
        ], JSON_PRETTY_PRINT);
    }

    public static function listAccesses()
    {
        $accesses = \Models\TempAccess::getAllForUser(request()->user['id']);

        response()->json([
            'status' => 'success',
            'message' => 'Liste des accès temporaires',
            'code' => 'TEMP_ACCESS/LIST',
            'data' => $accesses
        ], JSON_PRETTY_PRINT);
    }

    private static function randomPassword()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 16; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    public static function createAccess()
    {
        $site_id = input()->post("site_id", null);

        // One of the required parameters is missing
        if ($site_id === null) {
            response()->httpCode(400);
            response()->json([
                'status' => 'error',
                'message' => "Le paramètre 'site_id' est manquant !",
                'code' => 'MISSING_PARAMETER'
            ], JSON_PRETTY_PRINT);
        }

        // We check if the user has the right to access the site
        $access = \Models\UserSite::getFromSiteIdAndUserId($site_id->value, request()->user['id']);

        if ($access == null) {
            response()->httpCode(403);
            response()->json([
                'status' => 'error',
                'message' => "Vous n'avez pas accès à ce site !",
                'code' => 'USER_NOT_AUTHORIZED'
            ], JSON_PRETTY_PRINT);
        }

        // We check if the user already has a temporary access to the site
        $tempAccess = \Models\TempAccess::get($access->id);

        if ($tempAccess) {
            if ($tempAccess->expires_at > date("Y-m-d H:i:s")) {
                response()->httpCode(400);
                response()->json([
                    'status' => 'error',
                    'message' => "Vous avez déjà un accès temporaire à ce site !",
                    'code' => 'ACCESS/USER_ALREADY_HAS_ACCESS',
                    'data' => $tempAccess
                ], JSON_PRETTY_PRINT);
            } else {
                // We delete the old temporary access
                $tempAccess->delete();
            }
        }

        // We create a temporary access for the user
        $username = "ftp-" . $site_id->value . "-" . request()->user['id'];
        $password = self::randomPassword();
        $expiresAfter = $_ENV["ACCESS_DURATION"];

        $tempAccess = \Models\TempAccess::create($access->id, $username, sodium_crypto_pwhash_scryptsalsa208sha256_str($password, SODIUM_CRYPTO_PWHASH_SCRYPTSALSA208SHA256_OPSLIMIT_INTERACTIVE, SODIUM_CRYPTO_PWHASH_SCRYPTSALSA208SHA256_MEMLIMIT_INTERACTIVE), $expiresAfter);
        $tempAccess->password = $password;

        response()->json([
            'status' => 'success',
            'message' => "Accès temporaire créé !",
            'code' => 'ACCESS/TEMP_ACCESS_CREATED',
            'data' => $tempAccess
        ], JSON_PRETTY_PRINT);
    }

    public static function deleteAccess(string $accessId)
    {
        $access = \Models\TempAccess::get($accessId);

        if ($access == null) {
            response()->httpCode(404);
            response()->json([
                'status' => 'error',
                'message' => "L'accès temporaire n'existe pas !",
                'code' => 'ACCESS/ACCESS_NOT_FOUND'
            ], JSON_PRETTY_PRINT);
        }

        // We check if the user has the right to access the site
        $userSite = \Models\UserSite::get($accessId);

        if ($userSite == null || $userSite->user_id != request()->user['id']) {
            response()->httpCode(403);
            response()->json([
                'status' => 'error',
                'message' => "Vous n'avez pas accès à ce site !",
                'code' => 'USER_NOT_AUTHORIZED'
            ], JSON_PRETTY_PRINT);
        }

        $access->delete();

        response()->json([
            'status' => 'success',
            'message' => "Accès temporaire supprimé !",
            'code' => 'ACCESS/ACCESS_DELETED'
        ], JSON_PRETTY_PRINT);
    }

    public static function listAll()
    {
        $db = \Services\Database::getInstance();

        $stmt = $db->prepare("SELECT sites.id, sites.name, sites.uid, sites.dir, users_sites.id as access_id, users_sites.authorized_by, users_sites.authorized_at, temp_access.username, '****************' as password, temp_access.created_at, temp_access.expires_at FROM users_sites
            INNER JOIN sites ON users_sites.site_id = sites.id
            LEFT JOIN temp_access ON users_sites.id = temp_access.access_id
            WHERE users_sites.user_id = :user_id
            ORDER BY sites.id ASC");

        $username = request()->user['id'];
        $stmt->bindParam(":user_id", $username);
        $stmt->execute();
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $accesses = $stmt->fetchAll();

        response()->json([
            'status' => 'success',
            'message' => "Liste des accès",
            'code' => 'ACCESS/LIST',
            'data' => $accesses
        ], JSON_PRETTY_PRINT);
    }
}
