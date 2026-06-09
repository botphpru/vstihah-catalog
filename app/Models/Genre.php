<?php

namespace App\Models;

use App\Core\Application;
use App\Core\Model;

class Genre extends Model
{

    public int $count = 0;
    public static function getAllWithCount($cache = false): ?array
    {
        $db = Application::$app->db;
        $sql = 'SELECT g.*, COUNT(pg.genre_id) AS `count`
FROM `genres` g
LEFT JOIN `poem_genre` pg ON g.id = pg.genre_id
GROUP BY g.id;';
        return $db->fetchAll($sql, [], static::class, $cache);
    }

    public static function getByPoemId(int $poem_id, $cache = false): ?self
    {
        $db = \App\Core\Application::$app->db;
        $sql = "SELECT g.* 
            FROM `genres` g
            INNER JOIN `poem_genre` pg ON g.id = pg.genre_id
            WHERE pg.poem_id = :poem_id
            LIMIT 1";
        return $db->fetch($sql, [':poem_id' => $poem_id], static::class, $cache);
    }

    protected static function getTableName(): string
    {
        return 'genres';
    }
}