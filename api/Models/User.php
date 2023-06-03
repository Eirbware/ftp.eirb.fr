<?php

namespace FtpEirb\Models;

use FtpEirb\Services\Database;

class User
{
    public string $id;
    public string $first_name;
    public string $last_name;
    public int $admin;
    public ?string $added_by;

    /**
     * Get a user by its id
     *
     * @param string $id
     *
     * @return User|null
     */
    public static function get($id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetchObject(User::class) ?: null;
    }

    /**
     * Get all users
     *
     * @return array<User>
     */
    public static function getAll()
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_CLASS, User::class) ?: [];
    }

    /**
     * Get all sites for a user
     *
     * @return array<Site>
     */
    public function getSites()
    {
        return Site::getAllForUser($this->id);
    }

    /**
     * Save the user in the database
     *
     * @return bool
     */
    public function persist()
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO users (id, first_name, last_name, admin, added_by) VALUES (:id, :first_name, :last_name, :admin, :added_by)");
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":admin", $this->admin);
        $stmt->bindParam(":added_by", $this->added_by);
        return $stmt->execute();
    }

    /**
     * Update the user in the database
     *
     * @return bool
     */
    public function update()
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE users SET first_name = :first_name, last_name = :last_name, admin = :admin, added_by = :added_by WHERE id = :id");
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":admin", $this->admin);
        $stmt->bindParam(":added_by", $this->added_by);
        return $stmt->execute();
    }

    /**
     * Delete the user from the database
     *
     * @return bool
     */
    public function delete()
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }
}
