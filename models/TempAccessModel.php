<?php


namespace Models {

    use \Services\Database;

    class TempAccess
    {
        public int $access_id;
        public string $username;
        public string $password;
        public string $created_at;
        public string $expires_at;

        public static function get($id): ?TempAccess
        {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT * FROM temp_access WHERE access_id = :id");
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            $stmt->setFetchMode(\PDO::FETCH_CLASS, TempAccess::class);
            return $stmt->fetch() ?: null;
        }

        public static function getAll(): array
        {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT * FROM temp_access");
            $stmt->execute();
            $stmt->setFetchMode(\PDO::FETCH_CLASS, TempAccess::class);
            return $stmt->fetchAll();
        }

        public static function getAllForUser($user_id): array
        {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT temp_access.* FROM temp_access INNER JOIN users_sites ON users_sites.id = temp_access.access_id WHERE users_sites.user_id = :user_id");
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();
            $stmt->setFetchMode(\PDO::FETCH_CLASS, TempAccess::class);
            return $stmt->fetchAll();
        }

        public static function deleteAllForUser($user_id): bool
        {
            $db = Database::getInstance();
            $stmt = $db->prepare("DELETE FROM temp_access WHERE access_id IN (SELECT users_sites.id FROM users_sites WHERE users_sites.user_id = :user_id)");
            $stmt->bindParam(":user_id", $user_id);
            return $stmt->execute();
        }

        public static function create($access_id, $username, $password, $expires_after): ?TempAccess
        {
            $db = Database::getInstance();
            $db->query("SET time_zone = '+00:00'");
            $stmt = $db->prepare("INSERT INTO temp_access (access_id, username, password, expires_at) VALUES (:access_id, :username, :password, DATE_ADD(NOW(), INTERVAL :expires_after MINUTE))");
            $stmt->bindParam(":access_id", $access_id);
            $stmt->bindParam(":username", $username);
            $stmt->bindParam(":password", $password);
            $stmt->bindParam(":expires_after", $expires_after);
            $stmt->execute();
            return self::get($access_id);
        }

        public function delete()
        {
            $db = Database::getInstance();
            $stmt = $db->prepare("DELETE FROM temp_access WHERE access_id = :access_id");
            $stmt->bindParam(":access_id", $this->access_id);
            $stmt->execute();
        }
    }
}
