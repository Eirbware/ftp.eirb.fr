<?php

namespace FtpEirb\Controllers;

use FtpEirb\Models\Site;
use FtpEirb\Models\TempAccess;
use FtpEirb\Models\UserSite;
use FtpEirb\Services\Database;

class DashboardController
{
    public static function listSites(): bool
    {
        $sites = Site::getAllForUser(AuthController::$user->id);

        return success("Liste des sites", "SITE/LIST", $sites);
    }

    public static function listAccesses(): bool
    {
        $accesses = TempAccess::getAllForUser(AuthController::$user->id);

        return success("Liste des accès temporaires", "TEMP_ACCESS/LIST", $accesses);
    }

    private static function randomPassword(): string
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

    public static function createAccess(): bool
    {
        $siteId = input("site_id", null);

        // One of the required parameters is missing
        if ($siteId === null) {
            return error("Le paramètre 'site_id' est manquant !", "MISSING_PARAMETER");
        }

        // We check if the user has the right to access the site
        $access = UserSite::getFromSiteIdAndUserId($siteId, AuthController::$user->id);

        if ($access == null) {
            return error("Vous n'avez pas accès à ce site !", "USER_NOT_AUTHORIZED", 403);
        }

        // We check if the user already has a temporary access to the site
        $tempAccess = TempAccess::get($access->id);

        if ($tempAccess) {
            if ($tempAccess->expires_at > date("Y-m-d H:i:s")) {
                return error("Vous avez déjà un accès temporaire à ce site !", "USER_ALREADY_HAS_ACCESS");
            } else {
                // We delete the old temporary access
                $tempAccess->delete();
            }
        }

        // We create a temporary access for the user
        $username = "ftp-" . $siteId . "-" . AuthController::$user->id;
        $password = self::randomPassword();
        $expiresAfter = $_ENV["ACCESS_DURATION"];

        $tempAccess = TempAccess::create($access->id, $username, sodium_crypto_pwhash_scryptsalsa208sha256_str($password, SODIUM_CRYPTO_PWHASH_SCRYPTSALSA208SHA256_OPSLIMIT_INTERACTIVE, SODIUM_CRYPTO_PWHASH_SCRYPTSALSA208SHA256_MEMLIMIT_INTERACTIVE), $expiresAfter);
        
        if ($tempAccess == null) {
            return error("Impossible de créer l'accès temporaire !", "ACCESS/TEMP_ACCESS_CREATION_FAILED");
        }

        $tempAccess->password = $password;

        return success("Accès temporaire créé !", "ACCESS/TEMP_ACCESS_CREATED", $tempAccess);
    }

    public static function deleteAccess(int $accessId): bool
    {
        $access = TempAccess::get($accessId);

        if ($access == null) {
            return error("L'accès temporaire n'existe pas !", "ACCESS/ACCESS_NOT_FOUND", 404);
        }

        // We check if the user has the right to access the site
        $userSite = UserSite::get($accessId);

        if ($userSite == null || $userSite->user_id != AuthController::$user->id) {
            return error("Vous n'avez pas accès à ce site !", "USER_NOT_AUTHORIZED", 403);
        }

        $access->delete();

        return success("Accès temporaire supprimé !", "ACCESS/ACCESS_DELETED");
    }

    public static function listAll(): bool
    {
        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT sites.id, sites.name, sites.uid, sites.dir, users_sites.id as access_id, users_sites.authorized_by, users_sites.authorized_at, temp_access.username, '****************' as password, temp_access.created_at, temp_access.expires_at FROM users_sites
            INNER JOIN sites ON users_sites.site_id = sites.id
            LEFT JOIN temp_access ON users_sites.id = temp_access.access_id
            WHERE users_sites.user_id = :user_id
            ORDER BY sites.id ASC");

        $username = AuthController::$user->id;
        $stmt->bindParam(":user_id", $username);
        $stmt->execute();
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $accesses = $stmt->fetchAll();

        return success("Liste des accès", "ACCESS/LIST", $accesses);
    }
}
