<?php


namespace Models {

    use \Services\Database;

    class UserSite
    {
        public int $id;
        public string $user_id;
        public string $site_id;
        public string $authorized_by;
        public string $authorized_at;

        public static function get($id): ?UserSite
        {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT * FROM users_sites WHERE id = :id");
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            $stmt->setFetchMode(\PDO::FETCH_CLASS, UserSite::class);
            return $stmt->fetch() ?: null;
        }

        public static function getAll(): array
        {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT * FROM users_sites");
            $stmt->execute();
            $stmt->setFetchMode(\PDO::FETCH_CLASS, UserSite::class);
            return $stmt->fetchAll();
        }

        public static function getFromSiteIdAndUserId($site_id, $user_id): ?UserSite
        {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT * FROM users_sites WHERE site_id = :site_id AND user_id = :user_id");
            $stmt->bindParam(":site_id", $site_id);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();
            $stmt->setFetchMode(\PDO::FETCH_CLASS, UserSite::class);
            return $stmt->fetch() ?: null;
        }

        public function persist(): bool
        {
            $db = Database::getInstance();
            $stmt = $db->prepare("INSERT INTO users_sites (user_id, site_id, authorized_by, authorized_at) VALUES (:user_id, :site_id, :authorized_by, :authorized_at)");
            $stmt->bindParam(":user_id", $this->user_id);
            $stmt->bindParam(":site_id", $this->site_id);
            $stmt->bindParam(":authorized_by", $this->authorized_by);
            $stmt->bindParam(":authorized_at", $this->authorized_at);
            return $stmt->execute();
        }

        public function delete(): bool
        {
            $db = Database::getInstance();
            $stmt = $db->prepare("DELETE FROM users_sites WHERE id = :id");
            $stmt->bindParam(":id", $this->id);
            return $stmt->execute();
        }
    }
}
