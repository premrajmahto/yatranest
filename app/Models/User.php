<?php

namespace App\Models;

use App\Core\Model;

class User extends Model {
    protected string $table = 'users';

    public function findByEmail(string $email): ?array {
        return $this->findBy('email', strtolower(trim($email)));
    }

    public function getRoles(int $userId): array {
        $sql = "SELECT r.name FROM roles r
                JOIN user_roles ur ON r.id = ur.role_id
                WHERE ur.user_id = :uid";
        $rows = $this->fetchAll($sql, [':uid' => $userId]);
        return array_column($rows, 'name');
    }

    public function assignRole(int $userId, string $roleName): bool {
        $roleModel = new Role();
        $role = $roleModel->findBy('name', $roleName);
        if (!$role) return false;

        $sql = "INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (:uid, :rid)";
        return (bool)$this->query($sql, [':uid' => $userId, ':rid' => $role['id']]);
    }
}
