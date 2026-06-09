<?php

namespace App\Core;

use App\Core\Exceptions\BaseException;

abstract class Relation
{
    abstract protected static function getTableName(): string;

    public static function insertByArr(array $arr): void
    {
        $arrKeys = array_keys($arr);
        $values = [];
        $arrSql = [];
        foreach ($arrKeys as $arrKey) {
            $values[] = ':'.$arrKey;
            $arrSql[':'.$arrKey] = $arr[$arrKey];
        }
        $db = Application::$app->db;
        $sql = 'INSERT INTO ' . static::getTableName() . ' (' . implode(', ', $arrKeys) . ') VALUES (' . implode(', ', $values) . ');';
        $db->query($sql, $arrSql);
    }
    public static function deleteByArr(array $arr)
    {
        $columns2params = [];
        $params2values = [];
        $index = 1;
        foreach ($arr as $column => $value) {
            $param = ':param' . $index; // :param1
            $columns2params[] = $column . ' = ' . $param; // column1 = :param1
            $params2values[$param] = $value; // [:param1 => value1]
            $index++;
        }
        $sql = 'DELETE FROM ' . static::getTableName() . ' WHERE '.implode(' AND ', $columns2params).';';
        $db = Application::$app->db;
        return $db->fetchAll($sql, $params2values, static::class);
    }
    public static function getAllByArr(array $arr, $use_cache = false): ?array {
        $columns2params = [];
        $params2values = [];
        $index = 1;
        foreach ($arr as $column => $value) {
            $param = ':param' . $index; // :param1
            $columns2params[] = $column . ' = ' . $param; // column1 = :param1
            $params2values[$param] = $value; // [:param1 => value1]
            $index++;
        }
        $sql = 'SELECT * FROM ' . static::getTableName() . ' WHERE '.implode(' AND ', $columns2params).';';
        $db = Application::$app->db;
        return $db->fetchAll($sql, $params2values, static::class, $use_cache);
    }
    public static function getCountTotal($use_cache = false): ?int {

        $sql = 'SELECT COUNT(*) as cnt FROM ' . static::getTableName() . ';';
        $db = Application::$app->db;
        $res = $db->fetch($sql, [], \stdClass::class, $use_cache);
        return $res->cnt;
    }
    public static function getCountByArr(array $arr, $use_cache = false): ?int {
        $columns2params = [];
        $params2values = [];
        $index = 1;
        foreach ($arr as $column => $value) {
            $param = ':param' . $index; // :param1
            $columns2params[] = $column . ' = ' . $param; // column1 = :param1
            $params2values[$param] = $value; // [:param1 => value1]
            $index++;
        }
        $sql = 'SELECT COUNT(*) as cnt FROM ' . static::getTableName() . ' WHERE '.implode(' AND ', $columns2params).';';
        $db = Application::$app->db;
        $res = $db->fetch($sql, $params2values, \stdClass::class, $use_cache);
        return $res->cnt;
    }
    /**
     * @throws BaseException
     */
    public static function getOneByArr(array $arr, $use_cache = false): ?self {
        $columns2params = [];
        $params2values = [];
        $index = 1;
        foreach ($arr as $column => $value) {
            $param = ':param' . $index; // :param1
            $columns2params[] = $column . ' = ' . $param; // column1 = :param1
            $params2values[$param] = $value; // [:param1 => value1]
            $index++;
        }
        $sql = 'SELECT * FROM ' . static::getTableName() . ' WHERE '.implode(' AND ', $columns2params).';';
        $db = Application::$app->db;
        return $db->fetch($sql, $params2values, static::class, $use_cache);
    }
}