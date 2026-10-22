<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

/**
 * User Model
 */
class User extends Model
{
    protected static string $table = 'users';

    /**
     * Authenticate user with username and plain password
     */
    public static function authenticate(string $username, string $password): ?self
    {
        $user = self::findWhere('username', $username);
        if ($user && (int)$user->is_active === 1 && password_verify($password, (string)$user->password_hash)) {
            return $user;
        }

        return null;
    }

    /**
     * Get all active technicians
     *
     * @return self[]
     */
    public static function getTechnicians(): array
    {
        $sql = "SELECT * FROM `users` WHERE `role` = 'technician' AND `is_active` = 1 ORDER BY `full_name` ASC";
        $rows = Database::fetchAll($sql);

        return array_map(fn($r) => new self($r), $rows);
    }

    /**
     * Get all active teachers/staff
     *
     * @return self[]
     */
    public static function getTeachers(): array
    {
        $sql = "SELECT * FROM `users` WHERE `role` = 'teacher' AND `is_active` = 1 ORDER BY `full_name` ASC";
        $rows = Database::fetchAll($sql);

        return array_map(fn($r) => new self($r), $rows);
    }

    /**
     * Create user with hashed password
     */
    public static function registerUser(array $data): self
    {
        $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
        unset($data['password']);

        return self::create($data);
    }

    /**
     * Update password
     */
    public static function updatePassword(int $id, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        return self::update($id, ['password_hash' => $hash]);
    }
}
