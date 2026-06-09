<?php

namespace App\Models;

use App\Core\Application;
use App\Core\Model;

class Event extends Model
{

    public int $count = 0;
    public static function getAllWithCount($cache = false): ?array
    {
        $db = Application::$app->db;
        $sql = 'SELECT e.*, COUNT(pe.event_id) AS `count`
FROM `events` e
LEFT JOIN `poem_event` pe ON e.id = pe.event_id
GROUP BY e.id;';
        return $db->fetchAll($sql, [], static::class, $cache);
    }

    public static function getByPoemId(int $poem_id, $cache = false): ?self
    {
        $db = \App\Core\Application::$app->db;
        $sql = "SELECT e.* 
                FROM `events` e
                INNER JOIN `poem_event` pe ON e.id = pe.event_id
                WHERE pe.poem_id = :poem_id
                LIMIT 1";

        return $db->fetch($sql, [':poem_id' => $poem_id], static::class, $cache);
    }

    protected static function getTableName(): string
    {
        return 'events';
    }
}