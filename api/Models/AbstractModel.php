<?php

namespace FtpEirb\Models;

use FtpEirb\Services\Database;

/**
 * @template T
 */
abstract class AbstractModel
{
    /** @var array<string> */
    protected static $boolFields = [];

    /** @var array<string> */
    protected static $intFields = [];

    /** @var array<string> */
    protected static $dateFields = [];

    /** @var array<string> */
    protected static $idFields = ["id"];

    /** @var string */
    protected static $tableName = "";

    /**
     * @return void
     */
    public function __construct()
    {
        foreach (static::$boolFields as $field) {
            $this->$field = (bool) $this->$field;
        }

        foreach (static::$intFields as $field) {
            $this->$field = (int) $this->$field;
        }

        foreach (static::$dateFields as $field) {
            $this->$field = $this->$field ? str_replace(" ", "T", $this->$field) : $this->$field;
        }
    }

    /**
     * @return string
     */
    public static function getTableName()
    {
        $prefix = Database::getPrefix();
        return $prefix . static::$tableName;
    }

    /**
     * @param string|null $orderBy
     * @param int|null $limit
     * @return array<T>
     */
    public static function getAll($orderBy = null, $limit = null)
    {
        $tableName = static::getTableName();
        $sql = "SELECT * FROM $tableName";
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_CLASS, static::class) ?: [];
    }

    /**
     * @param int|string $id
     * @return T|null
     */
    public static function getById($id)
    {
        $tableName = static::getTableName();
        $sql = "SELECT * FROM $tableName WHERE " . static::$idFields[0] . " = :id";
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["id" => $id]);
        /** @var T|null */
        return $stmt->fetchObject(static::class) ?: null;
    }

    /**
     * @param array<string, mixed> $data
     * @return T|null
     */
    public static function getByFields($data)
    {
        return static::getAllByFields($data, null, 1)[0] ?? null;
    }

    /**
     * @param array<string, mixed> $data
     * @param string|null $orderBy
     * @param int|null $limit
     * @return array<T>
     */
    public static function getAllByFields($data, $orderBy = null, $limit = null)
    {
        $tableName = static::getTableName();
        $sql = "SELECT * FROM $tableName WHERE ";
        $sql .= implode(" = ? AND ", array_keys($data)) . " = ?";
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_values(static::sanitize($data)));
        return $stmt->fetchAll(\PDO::FETCH_CLASS, static::class) ?: [];
    }

    /**
     * @param string $field
     * @param array<int|string> $values
     * @param string|null $orderBy
     * @param int|null $limit
     * @return array<T>
     */
    public static function getAllByFieldIn($field, $values, $orderBy = null, $limit = null)
    {
        $tableName = static::getTableName();
        $values[] = -1;
        $sql = "SELECT * FROM $tableName WHERE $field IN (";
        $sql .= implode(", ", array_fill(0, count($values), "?")) . ")";
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_values($values));
        return $stmt->fetchAll(\PDO::FETCH_CLASS, static::class) ?: [];
    }

    /**
     * @param array<string, mixed> $data
     * @return T|null
     */
    public static function create($data)
    {
        $tableName = static::getTableName();
        $sql = "INSERT INTO $tableName (";
        $sql .= implode(", ", array_keys($data)) . ") VALUES (";
        $sql .= implode(", ", array_fill(0, count($data), "?")) . ")";
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_values(static::sanitize($data)));
        $id = $pdo->lastInsertId();
        if ($id) {
            return static::getById($id);
        } else {
            return null;
        }
    }

    /**
     * @param array<string, mixed> $data
     * @return T|null
     */
    public function update($data)
    {
        $tableName = static::getTableName();
        $sql = "UPDATE $tableName SET ";
        $sql .= implode(" = ?, ", array_keys($data)) . " = ? WHERE " . static::$idFields[0] . " = ?";
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_merge(array_values(static::sanitize($data)), [$this->{static::$idFields[0]}]));
        return static::getById($this->{static::$idFields[0]});
    }

    /**
     * @param bool $update
     * @return T|null
     */
    public function save($update = false)
    {
        $data = get_object_vars($this);

        if ($update) {
            return $this->update($data);
        } else {
            $entity = $this->create($data);
            if ($entity) {
                foreach (static::$idFields as $field) {
                    $this->$field = $entity->$field;
                }
            }
            return $entity;
        }
    }

    /**
     * @return bool
     */
    public function delete()
    {
        $tableName = static::getTableName();
        $sql = "DELETE FROM $tableName WHERE " . static::$idFields[0] . " = ?";
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$this->{static::$idFields[0]}]);
    }

    /**
     * @param array<string, mixed> $data
     * @return bool
     */
    public static function deleteAllByFields($data)
    {
        $tableName = static::getTableName();
        $sql = "DELETE FROM $tableName WHERE ";
        $sql .= implode(" = ? AND ", array_keys($data)) . " = ?";
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(array_values(static::sanitize($data)));
    }

    /**
     * @param string $field
     * @param array<int|string> $values
     * @return bool
     */
    public static function deleteAllByFieldIn($field, $values)
    {
        $tableName = static::getTableName();
        $values[] = -1;
        $sql = "DELETE FROM $tableName WHERE $field IN (";
        $sql .= implode(", ", array_fill(0, count($values), "?")) . ")";
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(array_values($values));
    }

    /**
     * @param array<string, mixed> $data
     * @return T|null
     */
    public static function createOrUpdate($data)
    {
        $model = static::getByFields($data);
        if ($model) {
            return $model->update($data);
        }
        return static::create($data);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected static function sanitize($data)
    {
        // Convert booleans to integers
        foreach ($data as $key => $value) {
            if (is_bool($value)) {
                $data[$key] = (int) $value;
            }
        }
        return $data;
    }
}
