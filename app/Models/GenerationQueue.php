<?php

namespace App\Models;

use App\Core\Model;

class GenerationQueue extends Model
{

    /**
     * Массовая вставка с игнорированием дублей (пачками по $chunkSize)
     * @param array $records массив ассоциативных массивов с данными
     * @param int $chunkSize размер пачки для одного запроса
     */
    public static function insertIgnoreBatch(array $records, int $chunkSize = 500): void
    {
        if (empty($records)) {
            return;
        }

        $db = \App\Core\Application::$app->db;
        $fields = array_keys($records[0]);
        $tableName = static::getTableName();

        // Разбиваем на пачки, чтобы не превысить лимит параметров MySQL
        $chunks = array_chunk($records, $chunkSize);

        foreach ($chunks as $chunk) {
            $valueSets = [];
            $allParams = [];
            $paramIndex = 1;

            foreach ($chunk as $record) {
                $placeholders = [];
                foreach ($fields as $field) {
                    $param = ':p' . $paramIndex++;
                    $placeholders[] = $param;
                    $allParams[$param] = $record[$field];
                }
                $valueSets[] = '(' . implode(', ', $placeholders) . ')';
            }

            $sql = 'INSERT IGNORE INTO `' . $tableName . '` 
                (' . implode(', ', $fields) . ') 
                VALUES ' . implode(', ', $valueSets);

            $db->query($sql, $allParams);
        }
    }
    protected static function getTableName(): string
    {
        return 'generation_queue';
    }
}