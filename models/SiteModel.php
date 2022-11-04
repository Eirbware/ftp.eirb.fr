<?php


namespace Models {

    use \Services\Database;

    class Site
    {
        public string $id;
        public string $name;
        public string $uid;
        public string $dir;

        public static function get($id): ?Site
        {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT * FROM sites WHERE id = :id");
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            $stmt->setFetchMode(\PDO::FETCH_CLASS, Site::class);
            return $stmt->fetch() ?: null;
        }

        public static function getAll(): array
        {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT * FROM sites");
            $stmt->execute();
            $stmt->setFetchMode(\PDO::FETCH_CLASS, Site::class);
            return $stmt->fetchAll();
        }

        public static function getAllForUser($user_id): array
        {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT sites.* FROM users_sites INNER JOIN sites ON users_sites.site_id = sites.id WHERE users_sites.user_id = :user_id");
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();
            $stmt->setFetchMode(\PDO::FETCH_CLASS, Site::class);
            return $stmt->fetchAll();
        }

        public function persist(): bool
        {
            $db = Database::getInstance();
            $stmt = $db->prepare("INSERT INTO sites (id, name, uid, dir) VALUES (:id, :name, :uid, :dir)");
            $stmt->bindParam(":id", $this->id);
            $stmt->bindParam(":name", $this->name);
            $stmt->bindParam(":uid", $this->uid);
            $stmt->bindParam(":dir", $this->dir);
            return $stmt->execute();
        }

        public function update(): bool
        {
            $db = Database::getInstance();
            $stmt = $db->prepare("UPDATE sites SET name = :name, uid = :uid, dir = :dir WHERE id = :id");
            $stmt->bindParam(":id", $this->id);
            $stmt->bindParam(":name", $this->name);
            $stmt->bindParam(":uid", $this->uid);
            $stmt->bindParam(":dir", $this->dir);
            return $stmt->execute();
        }

        public function delete(): bool
        {
            $db = Database::getInstance();
            $stmt = $db->prepare("DELETE FROM sites WHERE id = :id");
            $stmt->bindParam(":id", $this->id);
            return $stmt->execute();
        }
    }
}
