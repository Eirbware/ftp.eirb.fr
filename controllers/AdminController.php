<?php

class AdminController
{
    public static function listUsers()
    {
        $users = \Models\User::getAll();

        response()->json([
            'status' => 'success',
            'message' => "Liste des utilisateurs",
            'code' => 'ADMIN/USER_LIST',
            'data' => $users
        ], JSON_PRETTY_PRINT);
    }

    public static function createUser()
    {
        $id = input('id', null);
        $first_name = input('first_name', null);
        $last_name = input('last_name', null);
        $admin = input('admin', null);

        // Check that id is a string made of letters and numbers and that it is between 1 and 50 characters long
        if ($id === null || !preg_match("/^[a-z0-9]+$/" , $id) || strlen($id) < 1 || strlen($id) > 50) {
            response()->httpCode(400);
            response()->json([
                'status' => 'error',
                'message' => "Le paramètre 'id' doit être une chaîne de caractères alphanumériques de 1 à 50 caractères !",
                'code' => 'INVALID_ID'
            ], JSON_PRETTY_PRINT);
        }

        // Check that first_name is a string that it is between 1 and 100 characters long
        if ($first_name === null || !is_string($first_name) || strlen($first_name) < 1 || strlen($first_name) > 100) {
            response()->httpCode(400);
            response()->json([
                'status' => 'error',
                'message' => "Le paramètre 'first_name' doit être une chaîne de 1 à 100 caractères !",
                'code' => 'INVALID_FIRST_NAME'
            ], JSON_PRETTY_PRINT);
        }

        // Check that last_name is a string that it is between 1 and 100 characters long
        if ($last_name === null || !is_string($last_name) || strlen($last_name) < 1 || strlen($last_name) > 100) {
            response()->httpCode(400);
            response()->json([
                'status' => 'error',
                'message' => "Le paramètre 'last_name' doit être une chaîne de 1 à 100 caractères !",
                'code' => 'INVALID_LAST_NAME'
            ], JSON_PRETTY_PRINT);
        }

        // Check that admin is a boolean
        if ($admin === null || !is_bool($admin)) {
            response()->httpCode(400);
            response()->json([
                'status' => 'error',
                'message' => "Le paramètre 'admin' doit être un booléen !",
                'code' => 'INVALID_ADMIN'
            ], JSON_PRETTY_PRINT);
        }

        // We check if the user already exists
        if (\Models\User::get($id)) {
            response()->httpCode(400);
            response()->json([
                'status' => 'error',
                'message' => "Un utilisateur avec l'identifiant '$id' existe déjà !",
                'code' => 'USER_ALREADY_EXISTS'
            ], JSON_PRETTY_PRINT);
        }

        // We create the user
        $user = new \Models\User();
        $user->id = $id;
        $user->first_name = $first_name;
        $user->last_name = $last_name;
        $user->admin = $admin ? true : false;
        $user->added_by = request()->user['id'];

        try {
            $user->persist();
            response()->json([
                'status' => 'success',
                'message' => "L'utilisateur '$id' a été créé avec succès !",
                'code' => 'ADMIN/CREATE_USER',
                'data' => $user
            ], JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            response()->httpCode(500);
            response()->json([
                'status' => 'error',
                'message' => "Une erreur est survenue lors de la création de l'utilisateur '$id' !",
                'code' => 'ADMIN/CREATE_USER_ERROR'
            ], JSON_PRETTY_PRINT);
        }
    }

    public static function updateUser(string $userId)
    {
        $first_name = input('first_name', null);
        $last_name = input('last_name', null);
        $admin = input('admin', null);

        // Check that first_name is a string that it is between 1 and 100 characters long
        if ($first_name === null || !is_string($first_name) || strlen($first_name) < 1 || strlen($first_name) > 100) {
            response()->httpCode(400);
            response()->json([
                'status' => 'error',
                'message' => "Le paramètre 'first_name' doit être une chaîne de 1 à 100 caractères !",
                'code' => 'INVALID_FIRST_NAME'
            ], JSON_PRETTY_PRINT);
        }

        // Check that last_name is a string that it is between 1 and 100 characters long
        if ($last_name === null || !is_string($last_name) || strlen($last_name) < 1 || strlen($last_name) > 100) {
            response()->httpCode(400);
            response()->json([
                'status' => 'error',
                'message' => "Le paramètre 'last_name' doit être une chaîne de 1 à 100 caractères !",
                'code' => 'INVALID_LAST_NAME'
            ], JSON_PRETTY_PRINT);
        }

        // Check that admin is a boolean
        if ($admin === null || !is_bool($admin)) {
            response()->httpCode(400);
            response()->json([
                'status' => 'error',
                'message' => "Le paramètre 'admin' doit être un booléen !",
                'code' => 'INVALID_ADMIN'
            ], JSON_PRETTY_PRINT);
        }

        // We check if the user is not the current user
        if ($userId === request()->user['id'] && $admin === false) {
            response()->httpCode(400);
            response()->json([
                'status' => 'error',
                'message' => "Vous ne pouvez pas vous retirer les droits d'administrateur !",
                'code' => 'CANNOT_REMOVE_ADMIN_RIGHTS'
            ], JSON_PRETTY_PRINT);
        }

        // We check if the user exists
        $user = \Models\User::get($userId);
        if (!$user) {
            response()->httpCode(404);
            response()->json([
                'status' => 'error',
                'message' => "L'utilisateur '$userId' n'existe pas !",
                'code' => 'USER_DOES_NOT_EXIST'
            ], JSON_PRETTY_PRINT);
        }

        // We update the user
        $user->first_name = $first_name;
        $user->last_name = $last_name;
        $user->admin = $admin ? true : false;

        try {
            $user->update();
            response()->json([
                'status' => 'success',
                'message' => "L'utilisateur '$userId' a été mis à jour avec succès !",
                'code' => 'ADMIN/UPDATE_USER',
                'data' => $user
            ], JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            response()->httpCode(500);
            response()->json([
                'status' => 'error',
                'message' => "Une erreur est survenue lors de la mise à jour de l'utilisateur '$userId' !",
                'code' => 'ADMIN/UPDATE_USER_ERROR'
            ], JSON_PRETTY_PRINT);
        }
    }

    public static function deleteUser(string $userId)
    {
        // We check if the user is not the current user
        if ($userId === request()->user['id']) {
            response()->httpCode(400);
            response()->json([
                'status' => 'error',
                'message' => "Vous ne pouvez pas supprimer votre propre compte !",
                'code' => 'CANNOT_DELETE_YOURSELF'
            ], JSON_PRETTY_PRINT);
        }

        // We check if the user exists
        $user = \Models\User::get($userId);

        if (!$user) {
            response()->httpCode(400);
            response()->json([
                'status' => 'error',
                'message' => "L'utilisateur '$userId' n'existe pas !",
                'code' => 'USER_DOES_NOT_EXIST'
            ], JSON_PRETTY_PRINT);
        }

        // We delete the user
        
        try {
            $user->delete();
            response()->json([
                'status' => 'success',
                'message' => "L'utilisateur '$userId' a été supprimé avec succès !",
                'code' => 'ADMIN/DELETE_USER',
                'data' => $user
            ], JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            response()->httpCode(500);
            response()->json([
                'status' => 'error',
                'message' => "Une erreur est survenue lors de la suppression de l'utilisateur '$userId' !",
                'code' => 'ADMIN/DELETE_USER_ERROR'
            ], JSON_PRETTY_PRINT);
        }
    }

    public static function listSites()
    {
        $sites = \Models\Site::getAll();

        response()->json([
            'status' => 'success',
            'message' => "Liste des sites",
            'code' => 'ADMIN/SITE_LIST',
            'data' => $sites
        ], JSON_PRETTY_PRINT);
    }

    public static function createSite()
    {
        $id = input('id', null);
        $name = input('name', null);
        $uid = input('uid', null);
        $dir = input('dir', null);
        
        // Check that id is a string made of letters and numbers and that it is between 1 and 50 characters long
        if ($id === null || !preg_match("/^[a-z0-9]+$/" , $id) || strlen($id) < 1 || strlen($id) > 50) {
            response()->httpCode(400);
            response()->json([
                'status' => 'error',
                'message' => "Le paramètre 'id' doit être une chaîne de 1 à 50 caractères alphanumériques !",
                'code' => 'INVALID_ID'
            ], JSON_PRETTY_PRINT);
        }

        // Check that name is a string that it is between 1 and 100 characters long
        if ($name === null || !is_string($name) || strlen($name) < 1 || strlen($name) > 100) {
            response()->httpCode(400);
            response()->json([
                'status' => 'error',
                'message' => "Le paramètre 'name' doit être une chaîne de 1 à 100 caractères !",
                'code' => 'INVALID_NAME'
            ], JSON_PRETTY_PRINT);
        }

        // Check that uid is made of "www-" + the id
        if ($uid === null || $uid !== 'www-' . $id) {
            response()->httpCode(400);
            response()->json([
                'status' => 'error',
                'message' => "Le paramètre 'uid' doit être égal à 'www-$id' !",
                'code' => 'INVALID_UID'
            ], JSON_PRETTY_PRINT);
        }

        // Check that dir is a string that it is between 1 and 255 characters long
        if ($dir === null || !is_string($dir) || strlen($dir) < 1 || strlen($dir) > 255) {
            response()->httpCode(400);
            response()->json([
                'status' => 'error',
                'message' => "Le paramètre 'dir' doit être une chaîne de 1 à 255 caractères !",
                'code' => 'INVALID_DIR'
            ], JSON_PRETTY_PRINT);
        }

        // Check that dir is made of "/srv/web/sites/" + the id
        if ($dir !== '/srv/web/sites/' . $id) {
            response()->httpCode(400);
            response()->json([
                'status' => 'error',
                'message' => "Le paramètre 'dir' doit être égal à '/srv/web/sites/$id' !",
                'code' => 'INVALID_DIR'
            ], JSON_PRETTY_PRINT);
        }

        // We check if the site already exists
        if (\Models\Site::get($id)) {
            response()->httpCode(400);
            response()->json([
                'status' => 'error',
                'message' => "Le site '$id' existe déjà !",
                'code' => 'SITE_ALREADY_EXISTS'
            ], JSON_PRETTY_PRINT);
        }

        // We create the site
        $site = new \Models\Site();
        $site->id = $id;
        $site->name = $name;
        $site->uid = $uid;
        $site->dir = $dir;

        try {
            $site->persist();
            response()->json([
                'status' => 'success',
                'message' => "Le site '$id' a été créé avec succès !",
                'code' => 'ADMIN/CREATE_SITE',
                'data' => $site
            ], JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            response()->httpCode(500);
            response()->json([
                'status' => 'error',
                'message' => "Une erreur est survenue lors de la création du site '$id' !",
                'code' => 'ADMIN/CREATE_SITE_ERROR'
            ], JSON_PRETTY_PRINT);
        }
    }

    public static function updateSite(string $siteId)
    {
        $name = input('name', null);

        // Check that name is a string that it is between 1 and 100 characters long
        if ($name === null || !is_string($name) || strlen($name) < 1 || strlen($name) > 100) {
            response()->httpCode(400);
            response()->json([
                'status' => 'error',
                'message' => "Le paramètre 'name' doit être une chaîne de 1 à 100 caractères !",
                'code' => 'INVALID_NAME'
            ], JSON_PRETTY_PRINT);
        }

        // We check if the site exists
        $site = \Models\Site::get($siteId);
        if (!$site) {
            response()->httpCode(400);
            response()->json([
                'status' => 'error',
                'message' => "Le site '$siteId' n'existe pas !",
                'code' => 'SITE_DOES_NOT_EXIST'
            ], JSON_PRETTY_PRINT);
        }

        // We update the site
        $site->name = $name;

        try {
            $site->update();
            response()->json([
                'status' => 'success',
                'message' => "Le site '$siteId' a été mis à jour avec succès !",
                'code' => 'ADMIN/UPDATE_SITE',
                'data' => $site
            ], JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            response()->httpCode(500);
            response()->json([
                'status' => 'error',
                'message' => "Une erreur est survenue lors de la mise à jour du site '$siteId' !",
                'code' => 'ADMIN/UPDATE_SITE_ERROR'
            ], JSON_PRETTY_PRINT);
        }
    }

    public static function deleteSite(string $siteId)
    {
        // We check if the site exists
        $site = \Models\Site::get($siteId);

        if (!$site) {
            response()->httpCode(400);
            response()->json([
                'status' => 'error',
                'message' => "Le site '$siteId' n'existe pas !",
                'code' => 'SITE_DOES_NOT_EXIST'
            ], JSON_PRETTY_PRINT);
        }

        // We delete the site
        try {
            $site->delete();
            response()->json([
                'status' => 'success',
                'message' => "Le site '$siteId' a été supprimé avec succès !",
                'code' => 'ADMIN/DELETE_SITE',
                'data' => $site
            ], JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            response()->httpCode(500);
            response()->json([
                'status' => 'error',
                'message' => "Une erreur est survenue lors de la suppression du site '$siteId' !",
                'code' => 'ADMIN/DELETE_SITE_ERROR'
            ], JSON_PRETTY_PRINT);
        }
    }

    public static function listAccesses()
    {
        $accesses = \Models\UserSite::getAll();

        response()->json([
            'status' => 'success',
            'message' => 'Liste des accès aux sites',
            'code' => 'ADMIN/ACCESS_LIST',
            'data' => $accesses
        ], JSON_PRETTY_PRINT);
    }

    public static function createAccess()
    {
        $userId = input('user_id', null);
        $siteId = input('site_id', null);

        // One of the required parameters is missing
        if ($userId === null || $siteId === null) {
            response()->httpCode(400);
            response()->json([
                'status' => 'error',
                'message' => "Les paramètres 'user_id' et 'site_id' sont obligatoires !",
                'code' => 'MISSING_PARAMETER'
            ], JSON_PRETTY_PRINT);
        }

        // We check if the access, the user and the site exist
        $sql = 'SELECT (
            SELECT id FROM users_sites WHERE user_id = :user_id AND site_id = :site_id
        ) AS access_id, (
            SELECT id FROM users WHERE id = :user_id
        ) AS user_id, (
            SELECT id FROM sites WHERE id = :site_id
        ) AS site_id';
        $stmt = \Services\Database::getInstance()->prepare($sql);

        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':site_id', $siteId);

        $stmt->execute();

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($result['access_id']) {
            response()->httpCode(400);
            response()->json([
                'status' => 'error',
                'message' => "L'accès au site '$siteId' pour l'utilisateur '$userId' existe déjà !",
                'code' => 'ACCESS_ALREADY_EXISTS'
            ], JSON_PRETTY_PRINT);
        }

        if (!$result['user_id']) {
            response()->httpCode(400);
            response()->json([
                'status' => 'error',
                'message' => "L'utilisateur '$userId' n'existe pas !",
                'code' => 'USER_DOES_NOT_EXIST'
            ], JSON_PRETTY_PRINT);
        }

        if (!$result['site_id']) {
            response()->httpCode(400);
            response()->json([
                'status' => 'error',
                'message' => "Le site '$siteId' n'existe pas !",
                'code' => 'SITE_DOES_NOT_EXIST'
            ], JSON_PRETTY_PRINT);
        }

        // We create the access
        $access = new \Models\UserSite();
        $access->user_id = $userId;
        $access->site_id = $siteId;
        $access->authorized_by = request()->user['id'];
        $access->authorized_at = date('Y-m-d H:i:s');

        try {
            $access->persist();
            response()->json([
                'status' => 'success',
                'message' => "L'accès au site '$siteId' pour l'utilisateur '$userId' a été créé avec succès !",
                'code' => 'ADMIN/CREATE_ACCESS',
                'data' => $access
            ], JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            response()->httpCode(500);
            response()->json([
                'status' => 'error',
                'message' => "Une erreur est survenue lors de la création de l'accès au site '$siteId' pour l'utilisateur '$userId' !",
                'code' => 'ADMIN/CREATE_ACCESS_ERROR'
            ], JSON_PRETTY_PRINT);
        }
    }

    public static function deleteAccess(string $accessId)
    {
        // We check if the access exists
        $access = \Models\UserSite::get($accessId);

        if (!$access) {
            response()->httpCode(400);
            response()->json([
                'status' => 'error',
                'message' => "L'accès '$accessId' n'existe pas !",
                'code' => 'ACCESS_DOES_NOT_EXIST'
            ], JSON_PRETTY_PRINT);
        }

        // We delete the access
        try {
            $access->delete();
            response()->json([
                'status' => 'success',
                'message' => "L'accès '$accessId' a été supprimé avec succès !",
                'code' => 'ADMIN/DELETE_ACCESS',
                'data' => $access
            ], JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            response()->httpCode(500);
            response()->json([
                'status' => 'error',
                'message' => "Une erreur est survenue lors de la suppression de l'accès '$accessId' !",
                'code' => 'ADMIN/DELETE_ACCESS_ERROR'
            ], JSON_PRETTY_PRINT);
        }
    }

    public static function listAll()
    {
        $users = \Models\User::getAll();
        $sites = \Models\Site::getAll();
        $accesses = \Models\UserSite::getAll();

        response()->json([
            'status' => 'success',
            'message' => 'All fetched successfully',
            'code' => 'ADMIN/LIST_ALL',
            'data' => [
                'users' => array_map(function ($user) {
                    return [
                        'id' => $user->id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'admin' => $user->admin ? true : false,
                        'added_by' => $user->added_by ? $user->added_by : null,
                    ];
                }, $users),
                'sites' => $sites,
                'accesses' => $accesses
            ]
        ], JSON_PRETTY_PRINT);
    }
}
