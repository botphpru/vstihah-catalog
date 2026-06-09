<?php

namespace App\Models;

use App\Core\Application;
use App\Core\Model;

class Name extends Model
{
    public int $count = 0;
    public static function getAllWithCount($cache = false): ?array
    {
        $db = Application::$app->db;
        $sql = 'SELECT n.*, COUNT(pn.name_id) AS `count`
FROM `names` n
LEFT JOIN `poem_name` pn ON n.id = pn.name_id
GROUP BY n.id;';
        return $db->fetchAll($sql, [], static::class, $cache);
    }

    public static function getByPoemId(int $poem_id, $cache = false): ?self
    {
        $db = \App\Core\Application::$app->db;
        $sql = "SELECT n.* 
            FROM `names` n
            INNER JOIN `poem_name` pn ON n.id = pn.name_id
            WHERE pn.poem_id = :poem_id
            LIMIT 1";
        return $db->fetch($sql, [':poem_id' => $poem_id], static::class, $cache);
    }


    protected static function getTableName(): string
    {
        return 'names';
    }
}