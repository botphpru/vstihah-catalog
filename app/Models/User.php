<?php

namespace App\Models;

use App\Core\DB;
use App\Core\Exceptions\BaseException;
use App\Core\Model;

class User extends Model
{
    public int $id;
    public string $name;
    public string $email;
    public bool $is_confirmed;
    public string $role;
    public string $password_hash;
    public string $auth_token;
    public string $created_at;

    /**
     * @throws BaseException
     */
    public static function find(int $id): ?self
    {
        return app()->db->fetch(
            "SELECT * FROM users WHERE id = ?",
            [$id],
            self::class
        );
    }


    /**
     * @throws BaseException
     */
    public static function findByToken(string $token): ?self
    {
        return app()->db->fetch(
            "SELECT * FROM users WHERE auth_token = ?",
            [$token],
            self::class
        );
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password_hash);
    }

    public function generateToken(): string
    {
        $token = bin2hex(random_bytes(32));

        app()->db->update('users',
            ['auth_token' => $token],
            ['id' => $this->id]
        );

        $this->auth_token = $token;
        return $token;
    }

    public function clearToken(): void
    {
        app()->db->update('users',
            ['auth_token' => ''],
            ['id' => $this->id]
        );

        $this->auth_token = '';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isConfirmed(): bool
    {
        return (bool)$this->is_confirmed;
    }

    public static function create(array $data): bool
    {
        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['auth_token'] = '';
        $data['is_confirmed'] = 1; // Для админки сразу подтвержден
        $data['role'] = 'admin';   // Пока только админы

        unset($data['password']);

        return app()->db->insert('users', $data);
    }

    protected static function getTableName(): string
    {
        return 'users';
    }
}