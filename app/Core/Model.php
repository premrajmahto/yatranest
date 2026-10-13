<?php

namespace App\Core;

use PDO;

abstract class Model {
    protected string $table;
    protected string $primaryKey = 'id';

    public function getDb(): PDO {
        return Database::getConnection();
    }

    public function all(string $orderBy = 'id DESC'): array {
        $stmt = $this->getDb()->query("SELECT * FROM `{$this->table}` ORDER BY $orderBy");
        return $stmt->fetchAll();
    }

    public function find(int|string $id): ?array {
        $stmt = $this->getDb()->prepare("SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function findBy(string $column, mixed $value): ?array {
        $stmt = $this->getDb()->prepare("SELECT * FROM `{$this->table}` WHERE `$column` = :val LIMIT 1");
        $stmt->execute([':val' => $value]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function where(string $whereSql, array $params = [], string $orderBy = 'id DESC', ?int $limit = null, int $offset = 0): array {
        $sql = "SELECT * FROM `{$this->table}` WHERE $whereSql ORDER BY $orderBy";
        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function count(string $whereSql = '1=1', array $params = []): int {
        $stmt = $this->getDb()->prepare("SELECT COUNT(*) FROM `{$this->table}` WHERE $whereSql");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function create(array $data): int {
        $fields = array_keys($data);
        $columns = implode('`, `', $fields);
        $placeholders = ':' . implode(', :', $fields);

        $sql = "INSERT INTO `{$this->table}` (`$columns`) VALUES ($placeholders)";
        $stmt = $this->getDb()->prepare($sql);

        $params = [];
        foreach ($data as $key => $val) {
            $params[':' . $key] = $val;
        }

        $stmt->execute($params);
        return (int)$this->getDb()->lastInsertId();
    }

    public function update(int|string $id, array $data): bool {
        $fields = [];
        $params = [':id' => $id];

        foreach ($data as $key => $val) {
            $fields[] = "`$key` = :$key";
            $params[':' . $key] = $val;
        }

        $setClause = implode(', ', $fields);
        $sql = "UPDATE `{$this->table}` SET $setClause WHERE `{$this->primaryKey}` = :id";
        $stmt = $this->getDb()->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int|string $id): bool {
        $stmt = $this->getDb()->prepare("DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function query(string $sql, array $params = []): \PDOStatement {
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetchAll(string $sql, array $params = []): array {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetchOne(string $sql, array $params = []): ?array {
        $row = $this->query($sql, $params)->fetch();
        return $row ?: null;
    }

    public function lastInsertId(): int {
        return (int)$this->getDb()->lastInsertId();
    }

    public function beginTransaction(): bool {
        return Database::beginTransaction();
    }

    public function commit(): bool {
        return Database::commit();
    }

    public function rollBack(): bool {
        return Database::rollBack();
    }
}
