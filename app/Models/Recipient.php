<?php

namespace App\Models;

use App\Core\Application;
use App\Core\Model;

class Recipient extends Model
{
    public int $count = 0;
    public static function getAllWithCount($cache = false): ?array
    {
        $db = Application::$app->db;
        $sql = 'SELECT r.*, COUNT(pr.recipient_id) AS `count`
FROM `recipients` r
LEFT JOIN `poem_recipient` pr ON r.id = pr.recipient_id
GROUP BY r.id;';
        return $db->fetchAll($sql, [], static::class, $cache);
    }

    public static function getByPoemId(int $poem_id, $cache = false): ?self
    {
        $db = \App\Core\Application::$app->db;
        $sql = "SELECT r.* 
            FROM `recipients` r
            INNER JOIN `poem_recipient` pr ON r.id = pr.recipient_id
            WHERE pr.poem_id = :poem_id
            LIMIT 1";
        return $db->fetch($sql, [':poem_id' => $poem_id], static::class, $cache);
    }

    protected static function getTableName(): string
    {
        return 'recipients';
    }
}