<?php

namespace FtpEirb\Models;

use FtpEirb\Services\Database;

class Site
{
    public string $id;
    public string $name;
    public string $uid;
    public string $dir;

    /**
     * Get a site by its id
     *
     * @param string $id
     *
     * @return Site|null
     */
    public static function get($id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM sites WHERE id = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetchObject(Site::class) ?: null;
    }

    /**
     * Get all sites
     *
     * @return array<Site>
     */
    public static function getAll(): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM sites");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_CLASS, Site::class) ?: [];
    }

    /**
     * Get all sites for a user
     *
     * @param string $userId
     *
     * @return array<Site>
     */
    public static function getAllForUser($userId): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT sites.* FROM users_sites INNER JOIN sites ON users_sites.site_id = sites.id WHERE users_sites.user_id = :user_id");
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_CLASS, Site::class) ?: [];
    }

    /**
     * Save the site in the database
     *
     * @return bool
     */
    public function persist()
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO sites (id, name, uid, dir) VALUES (:id, :name, :uid, :dir)");
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":uid", $this->uid);
        $stmt->bindParam(":dir", $this->dir);
        return $stmt->execute();
    }

    /**
     * Update the site in the database
     *
     * @return bool
     */
    public function update()
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE sites SET name = :name, uid = :uid, dir = :dir WHERE id = :id");
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":uid", $this->uid);
        $stmt->bindParam(":dir", $this->dir);
        return $stmt->execute();
    }

    /**
     * Delete the site from the database
     *
     * @return bool
     */
    public function delete()
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM sites WHERE id = :id");
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }
}
