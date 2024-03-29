<?php

namespace FtpEirb\Services;

use FtpEirb\Models\Site;
use FtpEirb\Models\User;
use FtpEirb\Models\UserSite;

class Authentication
{
    /**
     * @var User|null
     */
    private static $user = null;

    public static function logout(): void
    {
        unset($_SESSION["user"]);
        self::$user = null;
    }

    /**
     * Get the current user
     *
     * @return User|null
     */
    public static function getCurrentUser()
    {
        if (self::$user == null && isset($_SESSION["user"])) {
            self::$user = User::getById($_SESSION["user"]["id"]);
        }
        return self::$user;
    }

    /**
     * Check if the current user is an admin
     *
     * @return bool
     */
    public static function isAdmin()
    {
        $currentUser = self::getCurrentUser();
        return $currentUser != null && $currentUser->admin == 1;
    }

    /**
     * Get the current user's sites
     *
     * @return array<\FtpEirb\Models\Site>
     */
    public static function listSites()
    {
        $currentUser = self::getCurrentUser();
        if ($currentUser != null) {
            $user_sites = UserSite::getAllByFields(["user_id" => $currentUser->id]);
            return Site::getAllByFieldIn("id", array_map(fn ($user_site) => $user_site->site_id, $user_sites));
        }
        return [];
    }
}
