<?php

namespace Services {

    use \Models\User;

    class Authentication
    {
        private static $user = null;

        public static function login($username, $password): bool
        {
            $user = User::get($username);
            if ($user != null) {
                if ($password == "#Password1234") {
                    $_SESSION["user"] = $user;
                    return true;
                }
            }
            return false;
        }

        public static function logout(): void
        {
            unset($_SESSION["user"]);
            self::$user = null;
        }

        public static function getCurrentUser(): ?User
        {
            if (self::$user == null) {
                if (isset($_SESSION["user"])) {
                    self::$user = $_SESSION["user"];
                }
            }
            return self::$user;
        }

        public static function isAdmin(): bool
        {
            $currentUser = self::getCurrentUser();
            return $currentUser != null && $currentUser->admin == 1;
        }

        public static function listSites(): array
        {
            $currentUser = self::getCurrentUser();
            if ($currentUser != null) {
                return $currentUser->getSites();
            }
            return [];
        }
    }
}
