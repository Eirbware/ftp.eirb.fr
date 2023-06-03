<?php

namespace FtpEirb\Models;

use FtpEirb\Services\Database;

class UserSite
{
    public int $id;
    public string $user_id;
    public string $site_id;
    public string $authorized_by;
    public string $authorized_at;

    /**
     * Get a user site by its id
     *
     * @param int $id
     *
     * @return UserSite|null
     */
    public static function get($id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users_sites WHERE id = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetchObject(UserSite::class) ?: null;
    }

    /**
     * Get all user sites
     *
     * @return array<UserSite>
     */
    public static function getAll(): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users_sites");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_CLASS, UserSite::class) ?: [];
    }

    /**
     * Get a user site by its site id and user id
     *
     * @param string $site_id
     * @param string $user_id
     *
     * @return UserSite|null
     */
    public static function getFromSiteIdAndUserId($site_id, $user_id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users_sites WHERE site_id = :site_id AND user_id = :user_id");
        $stmt->bindParam(":site_id", $site_id);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetchObject(UserSite::class) ?: null;
    }

    /**
     * Save the user site in the database
     *
     * @return bool
     */
    public function persist()
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO users_sites (user_id, site_id, authorized_by, authorized_at) VALUES (:user_id, :site_id, :authorized_by, :authorized_at)");
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":site_id", $this->site_id);
        $stmt->bindParam(":authorized_by", $this->authorized_by);
        $stmt->bindParam(":authorized_at", $this->authorized_at);
        return $stmt->execute();
    }

    /**
     * Delete the user site from the database
     *
     * @return bool
     */
    public function delete()
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM users_sites WHERE id = :id");
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }
}
