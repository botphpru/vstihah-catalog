<?php

namespace App\Models;

use App\Core\Application;
use App\Core\Model;

class Poem extends Model
{

    public static function getForPostingToTg()
    {
        $db = Application::$app->db;
        $sql = 'SELECT * FROM `' . static::getTableName() . '` WHERE is_tg_published = 0 ORDER BY RAND() LIMIT 1';
        return $db->fetch($sql, [], static::class, false);
    }
    /**
     * Получение стихов для каталога с фильтрацией, пагинацией и сортировкой
     */
    public static function getForCatalogPage(
        array $context,
        int $limit,
        int $offset,
        string $sort_value,
        $cache = false
    ): ?array {
        $db = Application::$app->db;

        // Базовый запрос с джойнами для фильтрации по связям
        $sql = "SELECT p.* 
                FROM poems p
                LEFT JOIN poem_event pe ON p.id = pe.poem_id
                LEFT JOIN poem_genre pg ON p.id = pg.poem_id
                LEFT JOIN poem_name pn ON p.id = pn.poem_id
                LEFT JOIN poem_recipient pr ON p.id = pr.poem_id";

        $conditions = [];
        $params = [];

        // Добавляем условия по контексту
        if ($context['event'] !== null) {
            $conditions[] = 'pe.event_id = :event_id';
            $params[':event_id'] = $context['event']->id;
        }
        if ($context['recipient'] !== null) {
            $conditions[] = 'pr.recipient_id = :recipient_id';
            $params[':recipient_id'] = $context['recipient']->id;
        }
        if ($context['name'] !== null) {
            $conditions[] = 'pn.name_id = :name_id';
            $params[':name_id'] = $context['name']->id;
        }
        if ($context['genre'] !== null) {
            $conditions[] = 'pg.genre_id = :genre_id';
            $params[':genre_id'] = $context['genre']->id;
        }

        // WHERE-условия
        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        // GROUP BY чтобы избежать дублей из-за джойнов
        $sql .= ' GROUP BY p.id';

        // Сортировка
        $orderBy = self::getSortOrder($sort_value);
        $sql .= ' ORDER BY ' . $orderBy;

        // Пагинация
        $sql .= ' LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;

        return $db->fetchAll($sql, $params, static::class, $cache);
    }

    /**
     * Подсчёт количества стихов по контексту
     */
    public static function getCountByContext(array $context, $cache = false): int
    {
        $db = Application::$app->db;

        $sql = "SELECT COUNT(DISTINCT p.id) as cnt 
                FROM poems p
                LEFT JOIN poem_event pe ON p.id = pe.poem_id
                LEFT JOIN poem_genre pg ON p.id = pg.poem_id
                LEFT JOIN poem_name pn ON p.id = pn.poem_id
                LEFT JOIN poem_recipient pr ON p.id = pr.poem_id";

        $conditions = [];
        $params = [];

        if ($context['event'] !== null) {
            $conditions[] = 'pe.event_id = :event_id';
            $params[':event_id'] = $context['event']->id;
        }
        if ($context['recipient'] !== null) {
            $conditions[] = 'pr.recipient_id = :recipient_id';
            $params[':recipient_id'] = $context['recipient']->id;
        }
        if ($context['name'] !== null) {
            $conditions[] = 'pn.name_id = :name_id';
            $params[':name_id'] = $context['name']->id;
        }
        if ($context['genre'] !== null) {
            $conditions[] = 'pg.genre_id = :genre_id';
            $params[':genre_id'] = $context['genre']->id;
        }

        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $result = $db->fetch($sql, $params, \stdClass::class, $cache);
        return (int)($result->cnt ?? 0);
    }

    /**
     * Преобразование значения сортировки в SQL ORDER BY
     * @private
     */
    private static function getSortOrder(string $sort_value): string
    {
        return match($sort_value) {
            'd_asc'  => 'p.id ASC',      // по дате добавления, старые сначала
            'd_desc' => 'p.id DESC',     // по дате, новые сначала (дефолт)
            'r_asc'  => 'p.rating ASC',  // по рейтингу, низкий сначала
            'r_desc' => 'p.rating DESC', // по рейтингу, высокий сначала
            'l_asc'  => 'p.quatrains ASC',  // по длине, короткие сначала
            'l_desc' => 'p.quatrains DESC', // по длине, длинные сначала
            default  => 'p.id DESC',     // фоллбэк
        };
    }

    /**
     * Преобразует дату из формата БД в русскоязычный формат "5 июня 2026"
     *
     * @return string Дата в формате "d месяц Y"
     */
    public function getRussianDate(): string
    {
        $timestamp = strtotime($this->add_at);

        if ($timestamp === false) {
            return ''; // или выбросить исключение
        }

        $months = [
            1 => 'января',
            2 => 'февраля',
            3 => 'марта',
            4 => 'апреля',
            5 => 'мая',
            6 => 'июня',
            7 => 'июля',
            8 => 'августа',
            9 => 'сентября',
            10 => 'октября',
            11 => 'ноября',
            12 => 'декабря'
        ];

        $day = date('j', $timestamp);  // день без ведущего нуля
        $month = (int) date('n', $timestamp); // номер месяца
        $year = date('Y', $timestamp);   // год

        return $day . ' ' . $months[$month] . ' ' . $year;
    }

    protected static function getTableName(): string
    {
        return 'poems';
    }
}