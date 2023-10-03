<?php

namespace FtpEirb\Controllers;

use FtpEirb\Models\Site;
use FtpEirb\Models\User;
use FtpEirb\Models\UserSite;

class AdminController
{
    public static function listUsers(): bool
    {
        $users = User::getAll();

        return success("Liste des utilisateurs", "ADMIN/USER_LIST", $users);
    }

    public static function createUser(): bool
    {
        $id = input('id', null);
        $firstName = input('first_name', null);
        $lastName = input('last_name', null);
        $details = input('details', null);
        $admin = input('admin', null);

        // Check that id is a string made of letters and numbers and that it is between 1 and 50 characters long
        if ($id === null || !preg_match("/^[a-z0-9]+$/", $id) || strlen($id) < 1 || strlen($id) > 50) {
            return error("Le paramètre 'id' doit être une chaîne de caractères alphanumériques de 1 à 50 caractères !", "INVALID_ID");
        }

        // Check that first_name is a string that it is between 1 and 100 characters long
        if ($firstName === null || !is_string($firstName) || strlen($firstName) < 1 || strlen($firstName) > 100) {
            return error("Le paramètre 'first_name' doit être une chaîne de 1 à 100 caractères !", "INVALID_FIRST_NAME");
        }

        // Check that last_name is a string that it is between 1 and 100 characters long
        if ($lastName === null || !is_string($lastName) || strlen($lastName) < 1 || strlen($lastName) > 100) {
            return error("Le paramètre 'last_name' doit être une chaîne de 1 à 100 caractères !", "INVALID_LAST_NAME");
        }

        // Check that if details is set, it is a string that it is between 1 and 255 characters long
        if ($details !== null && (!is_string($details) || strlen($details) < 1 || strlen($details) > 255)) {
            return error("Le paramètre 'details' doit être une chaîne de 1 à 255 caractères !", "INVALID_DETAILS");
        }

        // Check that admin is a boolean
        if ($admin === null) {
            return error("Le paramètre 'admin' doit être un booléen !", "INVALID_ADMIN");
        }

        // We check if the user already exists
        if (User::getById($id)) {
            return error("Un utilisateur avec l'identifiant '$id' existe déjà !", "USER_ALREADY_EXISTS");
        }

        // We create the user
        $user = new User();
        $user->id = $id;
        $user->first_name = $firstName;
        $user->last_name = $lastName;
        $user->details = $details;
        $user->admin = $admin ? true : false;
        $user->added_by = AuthController::$user->id;

        try {
            $user->save();
            return success("L'utilisateur '$id' a été créé avec succès !", "ADMIN/CREATE_USER", $user);
        } catch (\Exception $e) {
            logError($e->getMessage());
            return error("Une erreur est survenue lors de la création de l'utilisateur '$id' !", "ADMIN/CREATE_USER_ERROR", 500);
        }
    }

    public static function updateUser(string $userId): bool
    {
        $firstName = input('first_name', null);
        $lastName = input('last_name', null);
        $details = input('details', null);
        $admin = input('admin', null);

        // Check that first_name is a string that it is between 1 and 100 characters long
        if ($firstName === null || !is_string($firstName) || strlen($firstName) < 1 || strlen($firstName) > 100) {
            return error("Le paramètre 'first_name' doit être une chaîne de 1 à 100 caractères !", "INVALID_FIRST_NAME");
        }

        // Check that last_name is a string that it is between 1 and 100 characters long
        if ($lastName === null || !is_string($lastName) || strlen($lastName) < 1 || strlen($lastName) > 100) {
            return error("Le paramètre 'last_name' doit être une chaîne de 1 à 100 caractères !", "INVALID_LAST_NAME");
        }

        // Check that if details is set, it is a string that it is between 1 and 255 characters long
        if ($details !== null && (!is_string($details) || strlen($details) < 1 || strlen($details) > 255)) {
            return error("Le paramètre 'details' doit être une chaîne de 1 à 255 caractères !", "INVALID_DETAILS");
        }

        // Check that admin is a boolean
        if ($admin === null) {
            return error("Le paramètre 'admin' doit être un booléen !", "INVALID_ADMIN");
        }

        // We check if the user is not the current user
        if ($userId === AuthController::$user->id && !$admin) {
            return error("Vous ne pouvez pas vous retirer les droits d'administrateur !", "CANNOT_REMOVE_ADMIN_RIGHTS");
        }

        // We check if the user exists
        $user = User::getById($userId);
        if (!$user) {
            return error("L'utilisateur '$userId' n'existe pas !", "USER_DOES_NOT_EXIST", 404);
        }

        // We update the user
        $user->first_name = $firstName;
        $user->last_name = $lastName;
        $user->details = $details;
        $user->admin = $admin ? true : false;

        try {
            $user->save(true);
            return success("L'utilisateur '$userId' a été mis à jour avec succès !", "ADMIN/UPDATE_USER", $user);
        } catch (\Exception $e) {
            logError($e->getMessage());
            return error("Une erreur est survenue lors de la mise à jour de l'utilisateur '$userId' !", "ADMIN/UPDATE_USER_ERROR", 500);
        }
    }

    public static function deleteUser(string $userId): bool
    {
        // We check if the user is not the current user
        if ($userId === AuthController::$user->id) {
            return error("Vous ne pouvez pas supprimer votre propre compte !", "CANNOT_DELETE_YOURSELF");
        }

        // We check if the user exists
        $user = User::getById($userId);

        if (!$user) {
            return error("L'utilisateur '$userId' n'existe pas !", "USER_DOES_NOT_EXIST");
        }

        // We delete the user

        try {
            $user->delete();
            return success("L'utilisateur '$userId' a été supprimé avec succès !", "ADMIN/DELETE_USER", $user);
        } catch (\Exception $e) {
            logError($e->getMessage());
            return error("Une erreur est survenue lors de la suppression de l'utilisateur '$userId' !", "ADMIN/DELETE_USER_ERROR", 500);
        }
    }

    public static function listSites(): bool
    {
        $sites = Site::getAll();

        return success("Liste des sites", "ADMIN/SITE_LIST", $sites);
    }

    public static function createSite(): bool
    {
        $id = input('id', null);
        $name = input('name', null);
        $uid = input('uid', null);
        $dir = input('dir', null);

        // Check that id is a string made of letters and numbers and that it is between 1 and 50 characters long
        if ($id === null || !preg_match("/^[a-z0-9]+$/", $id) || strlen($id) < 1 || strlen($id) > 50) {
            return error("Le paramètre 'id' doit être une chaîne de 1 à 50 caractères alphanumériques !", "INVALID_ID");
        }

        // Check that name is a string that it is between 1 and 100 characters long
        if ($name === null || !is_string($name) || strlen($name) < 1 || strlen($name) > 100) {
            return error("Le paramètre 'name' doit être une chaîne de 1 à 100 caractères !", "INVALID_NAME");
        }

        // Check that uid is made of "www-" + the id
        if ($uid === null || $uid !== 'www-' . $id) {
            return error("Le paramètre 'uid' doit être égal à 'www-$id' !", "INVALID_UID");
        }

        // Check that dir is a string that it is between 1 and 255 characters long
        if ($dir === null || !is_string($dir) || strlen($dir) < 1 || strlen($dir) > 255) {
            return error("Le paramètre 'dir' doit être une chaîne de 1 à 255 caractères !", "INVALID_DIR");
        }

        // Check that dir is made of "/srv/web/sites/" + the id
        if ($dir !== '/srv/web/sites/' . $id) {
            return error("Le paramètre 'dir' doit être égal à '/srv/web/sites/$id' !", "INVALID_DIR");
        }

        // We check if the site already exists
        if (Site::getById($id)) {
            return error("Le site '$id' existe déjà !", "SITE_ALREADY_EXISTS");
        }

        // We create the site
        $site = new Site();
        $site->id = $id;
        $site->name = $name;
        $site->uid = $uid;
        $site->dir = $dir;

        try {
            $site->save();
            return success("Le site '$id' a été créé avec succès !", "ADMIN/CREATE_SITE", $site);
        } catch (\Exception $e) {
            logError($e->getMessage());
            return error("Une erreur est survenue lors de la création du site '$id' !", "ADMIN/CREATE_SITE_ERROR", 500);
        }
    }

    public static function updateSite(string $siteId): bool
    {
        $name = input('name', null);

        // Check that name is a string that it is between 1 and 100 characters long
        if ($name === null || !is_string($name) || strlen($name) < 1 || strlen($name) > 100) {
            return error("Le paramètre 'name' doit être une chaîne de 1 à 100 caractères !", "INVALID_NAME");
        }

        // We check if the site exists
        $site = Site::getById($siteId);
        if (!$site) {
            return error("Le site '$siteId' n'existe pas !", "SITE_DOES_NOT_EXIST");
        }

        // We update the site
        $site->name = $name;

        try {
            $site->save(true);
            return success("Le site '$siteId' a été mis à jour avec succès !", "ADMIN/UPDATE_SITE", $site);
        } catch (\Exception $e) {
            logError($e->getMessage());
            return error("Une erreur est survenue lors de la mise à jour du site '$siteId' !", "ADMIN/UPDATE_SITE_ERROR", 500);
        }
    }

    public static function deleteSite(string $siteId): bool
    {
        // We check if the site exists
        $site = Site::getById($siteId);

        if (!$site) {
            return error("Le site '$siteId' n'existe pas !", "SITE_DOES_NOT_EXIST");
        }

        // We delete the site
        try {
            $site->delete();
            return success("Le site '$siteId' a été supprimé avec succès !", "ADMIN/DELETE_SITE", $site);
        } catch (\Exception $e) {
            logError($e->getMessage());
            return error("Une erreur est survenue lors de la suppression du site '$siteId' !", "ADMIN/DELETE_SITE_ERROR", 500);
        }
    }

    public static function listAccesses(): bool
    {
        $accesses = UserSite::getAll();

        return success("Liste des accès aux sites", "ADMIN/ACCESS_LIST", $accesses);
    }

    public static function createAccess(): bool
    {
        $userId = input('user_id', null);
        $siteId = input('site_id', null);

        // One of the required parameters is missing
        if ($userId === null || $siteId === null) {
            return error("Les paramètres 'user_id' et 'site_id' sont obligatoires !", "MISSING_PARAMETER");
        }

        // We check if the access, the user and the site exist
        $userSite = UserSite::getByFields(['user_id' => $userId, 'site_id' => $siteId]);
        $user = User::getById($userId);
        $site = Site::getById($siteId);

        if ($userSite) {
            return error("L'accès au site '$siteId' pour l'utilisateur '$userId' existe déjà !", "ACCESS_ALREADY_EXISTS");
        }

        if (!$user) {
            return error("L'utilisateur '$userId' n'existe pas !", "USER_DOES_NOT_EXIST");
        }

        if (!$site) {
            return error("Le site '$siteId' n'existe pas !", "SITE_DOES_NOT_EXIST");
        }

        // We create the access
        $access = new UserSite();
        $access->user_id = $userId;
        $access->site_id = $siteId;
        $access->authorized_by = AuthController::$user->id;
        $access->authorized_at = date('Y-m-d H:i:s');

        try {
            $access->save();
            return success("L'accès au site '$siteId' pour l'utilisateur '$userId' a été créé avec succès !", "ADMIN/CREATE_ACCESS", $access);
        } catch (\Exception $e) {
            logError($e->getMessage());
            return error("Une erreur est survenue lors de la création de l'accès au site '$siteId' pour l'utilisateur '$userId' !", "ADMIN/CREATE_ACCESS_ERROR", 500);
        }
    }

    public static function deleteAccess(string $accessId): bool
    {
        // We check if the access exists
        $access = UserSite::getById($accessId);

        if (!$access) {
            return error("L'accès '$accessId' n'existe pas !", "ACCESS_DOES_NOT_EXIST");
        }

        // We delete the access
        try {
            $access->delete();
            return success("L'accès '$accessId' a été supprimé avec succès !", "ADMIN/DELETE_ACCESS", $access);
        } catch (\Exception $e) {
            logError($e->getMessage());
            return error("Une erreur est survenue lors de la suppression de l'accès '$accessId' !", "ADMIN/DELETE_ACCESS_ERROR", 500);
        }
    }

    public static function listAll(): bool
    {
        $users = User::getAll();
        $sites = Site::getAll();
        $accesses = UserSite::getAll();

        return success("Liste des utilisateurs, des sites et des accès", "ADMIN/LIST_ALL", [
            'users' => array_map(function ($user) {
                return [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'details' => $user->details,
                    'admin' => $user->admin ? true : false,
                    'added_by' => $user->added_by ? $user->added_by : null,
                ];
            }, $users),
            'sites' => $sites,
            'accesses' => $accesses
        ]);
    }
}
