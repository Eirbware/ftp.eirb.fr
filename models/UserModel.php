<?php


namespace Models {

    use \Services\Database;

    class User
    {
        public string $id;
        public string $first_name;
        public string $last_name;
        public int $admin;
        public ?string $added_by;

        public static function get($id): ?User
        {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            $stmt->setFetchMode(\PDO::FETCH_CLASS, User::class);
            return $stmt->fetch() ?: null;
        }

        public static function getAll(): array
        {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT * FROM users");
            $stmt->execute();
            $stmt->setFetchMode(\PDO::FETCH_CLASS, User::class);
            return $stmt->fetchAll();
        }

        public function getSites(): array
        {
            return Site::getAllForUser($this->id);
        }

        public function persist(): bool
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

        public function update(): bool
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

        public function delete(): bool
        {
            $db = Database::getInstance();
            $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
            $stmt->bindParam(":id", $this->id);
            return $stmt->execute();
        }
    }
}
