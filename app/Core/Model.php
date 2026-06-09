<?php

namespace App\Core;

use App\Core\Db;
use App\Core\Exceptions\BaseException;

/**
 * Базовый абстрактный класс для всех моделей, связанных с таблицами БД.
 *
 * Предоставляет универсальные методы для выполнения типовых операций:
 * - Поиск записей по различным критериям (id, alias, slug, произвольные условия)
 * - Пагинация и сортировка результатов
 * - Вставка, обновление, удаление записей
 * - Подсчёт количества записей
 * - Опциональное кэширование результатов запросов
 *
 * Каждая модель-наследник должна реализовать метод getTableName()
 * для указания имени своей таблицы в базе данных.
 *
 * @package App\Core
 * @abstract
 */
abstract class Model
{
    /** @var int Уникальный идентификатор записи (первичный ключ) */
    public int $id;

    /**
     * Возвращает имя таблицы БД, с которой работает модель.
     *
     * Должен быть реализован в каждом классе-наследнике.
     * Используется во всех методах для формирования SQL-запросов.
     *
     * @return string Имя таблицы (без префиксов, например 'users', 'blog_posts')
     */
    abstract protected static function getTableName(): string;

    /**
     * Преобразует массив объектов в ассоциативный массив, где ключ — id объекта.
     *
     * Удобно для быстрого доступа к записям по идентификатору без поиска в цикле.
     *
     * Пример:
     *   Вход: [ {id: 5, name: 'A'}, {id: 10, name: 'B'} ]
     *   Выход: [ 5 => {id: 5, name: 'A'}, 10 => {id: 10, name: 'B'} ]
     *
     * @param array|null $array Массив объектов с свойством id
     *
     * @return array|null Ассоциативный массив [id => object] или null если вход пуст
     */
    public static function getRepo($array) {
        if(empty($array)) return null;

        $result = [];
        foreach ($array as $item) {
            $result[$item->id] = $item;
        }
        return $result;
    }

    /**
     * Преобразует массив объектов в ассоциативный массив, где ключ — поле slug объекта.
     *
     * Удобно для доступа к записям по человеко-читаемому идентификатору (slug).
     *
     * @param array|null $array Массив объектов с свойством slug
     *
     * @return array|null Ассоциативный массив [slug => object] или null если вход пуст
     */
    public static function getRepoSlug($array): ?array {
        if(empty($array)) return null;

        $result = [];
        foreach ($array as $item) {
            $result[$item->slug] = $item;
        }
        return $result;
    }

    /**
     * Увеличивает счётчик просмотров (view_count) для текущей записи.
     *
     * Выполняет прямой SQL-запрос:
     *   UPDATE {table} SET view_count = view_count + 1 WHERE id = :id
     *
     * @return void
     */
    public function updateViewCount()
    {
        $sql = 'UPDATE ' . static::getTableName() . ' 
SET view_count = view_count + 1 
WHERE id = :id';
        $db = Application::$app->db;
        $db->query($sql, [':id' => $this->id]);
    }

    /**
     * Получает все записи из таблицы.
     *
     * @param bool $cache Включить ли кэширование результата
     *
     * @return array<object>|null Массив объектов текущего класса или null если записей нет
     */
    public static function findAll($cache = false): ?array
    {
        $db = Application::$app->db;
        $sql = 'SELECT * FROM `' . static::getTableName() . '`';
        return $db->fetchAll($sql, [], static::class, $cache);
    }

    /**
     * Получает записи с пагинацией, сортировкой по id и опциональным исключением одной записи.
     *
     * Формирует запрос:
     *   SELECT * FROM {table} [WHERE id != $without_id] ORDER BY id {sort} LIMIT $limit OFFSET $offset
     *
     * ⚠️ Параметры $sort, $limit, $offset, $without_id подставляются напрямую в SQL.
     * Убедитесь, что они содержат только допустимые значения (ASC/DESC, целые числа).
     *
     * @param int $limit Количество записей на странице
     * @param int $offset Смещение первой записи
     * @param string $sort Направление сортировки: 'ASC' или 'DESC' (по умолчанию 'DESC')
     * @param bool $cache Включить ли кэширование
     * @param int|null $without_id ID записи, которую нужно исключить из выборки
     *
     * @return array<object>|null Массив объектов или null если записей нет
     */
    public static function getLimitOffsetByOrderId(int $limit, int $offset, string $sort = 'DESC', $cache = false, $without_id = null): ?array
    {
        $db = Application::$app->db;
        $sort = $sort == 'DESC' ? 'DESC' : 'ASC';
        $where = $without_id == null ? '' : ' WHERE id != '.$without_id;
        $sql = 'SELECT * FROM `' . static::getTableName(). '`' . $where.' ORDER BY `id` '.$sort.' LIMIT '.$limit.' OFFSET '.$offset;
        return $db->fetchAll($sql, [], static::class, $cache);
    }

    /**
     * Получает ограниченное количество записей с сортировкой по id и опциональным исключением.
     *
     * Аналогично getLimitOffsetByOrderId(), но без OFFSET (начинает с первой записи).
     *
     * @param int $limit Максимальное количество записей
     * @param string $sort Направление сортировки: 'ASC' или 'DESC'
     * @param bool $cache Включить ли кэширование
     * @param int|null $without_id ID записи для исключения
     *
     * @return array<object>|null Массив объектов или null
     */
    public static function getLimitByOrderId(int $limit, string $sort = 'DESC', $cache = false, $without_id = null): ?array
    {
        $db = Application::$app->db;
        $sort = $sort == 'DESC' ? 'DESC' : 'ASC';
        $where = $without_id == null ? '' : ' WHERE id != '.$without_id;
        $sql = 'SELECT * FROM `' . static::getTableName(). '`' . $where.' ORDER BY `id` '.$sort.' LIMIT '.$limit;
        return $db->fetchAll($sql, [], static::class, $cache);
    }

    /**
     * Получает все записи с сортировкой по id.
     *
     * @param string $sort Направление сортировки: 'ASC' или 'DESC'
     * @param bool $cache Включить ли кэширование
     *
     * @return array<object>|null Массив объектов или null
     */
    public static function getAllByOrderId(string $sort = 'DESC', $cache = false): ?array
    {
        $db = Application::$app->db;
        $sort = $sort == 'DESC' ? 'DESC' : 'ASC';
        $sql = 'SELECT * FROM `' . static::getTableName() . '` ORDER BY `id` '.$sort;
        return $db->fetchAll($sql, [], static::class, $cache);
    }

    /**
     * Получает одну запись по её ID.
     *
     * @param int $id Идентификатор записи
     * @param bool $cache Включить ли кэширование
     *
     * @return static|null Объект текущего класса или null если не найдено
     */
    public static function getById(int $id, $cache = false): ?self
    {
        $db = Application::$app->db;
        $sql = 'SELECT * FROM `' . static::getTableName() . '` WHERE id = :id';
        return $db->fetch($sql, [':id' => $id], static::class, $cache);
    }

    /**
     * Получает одну запись по полю alias.
     *
     * @param string $alias Значение поля alias
     * @param bool $cache Включить ли кэширование
     *
     * @return static|null Объект текущего класса или null если не найдено
     */
    public static function findByAlias(string $alias, $cache = false): ?self
    {
        $db = Application::$app->db;
        $sql = 'SELECT * FROM `' . static::getTableName() . '` WHERE alias = :alias';
        return $db->fetch($sql, [':alias' => $alias], static::class, $cache);
    }

    /**
     * Получает одну запись по полю slug.
     *
     * ⚠️ В реализации используется параметр :slug, но в массиве параметров
     * ключ также :slug (несмотря на имя аргумента $alias). Это корректно,
     * но может сбивать с толку при чтении кода.
     *
     * @param string $alias Значение поля slug (имя параметра историческое)
     * @param bool $cache Включить ли кэширование
     *
     * @return static|null Объект текущего класса или null если не найдено
     */
    public static function findBySlug(string $alias, $cache = false): ?self
    {
        $db = Application::$app->db;
        $sql = 'SELECT * FROM `' . static::getTableName() . '` WHERE slug = :slug';
        return $db->fetch($sql, [':slug' => $alias], static::class, $cache);
    }

    /**
     * Выполняет INSERT запрос с данными из ассоциативного массива.
     *
     * Автоматически формирует запрос:
     *   INSERT INTO {table} (col1, col2) VALUES (:col1, :col2)
     *
     * @param array $arr Данные для вставки [колонка => значение]
     *
     * @return void
     */
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

    /**
     * Выполняет DELETE запрос по условиям из массива.
     *
     * Формирует запрос:
     *   DELETE FROM {table} WHERE col1 = :param1 AND col2 = :param2
     *
     * ⚠️ Возвращает результат fetchAll(), хотя для DELETE логичнее ожидать
     * количество затронутых строк или булево значение. Проверьте, как используется
     * возвращаемое значение в вашем коде.
     *
     * @param array $arr Условия удаления [колонка => значение]
     *
     * @return array|mixed Результат выполнения fetchAll() (зависит от реализации DB)
     */
    public static function deleteByArr(array $arr)
    {
        $columns2params = [];
        $params2values = [];
        $index = 1;
        foreach ($arr as $column => $value) {
            $param = ':param' . $index;
            $columns2params[] = $column . ' = ' . $param;
            $params2values[$param] = $value;
            $index++;
        }
        $sql = 'DELETE FROM ' . static::getTableName() . ' WHERE '.implode(' AND ', $columns2params).';';
        $db = Application::$app->db;
        return $db->fetchAll($sql, $params2values, static::class);
    }

    /**
     * Обновляет поля текущей записи по её id.
     *
     * Формирует запрос:
     *   UPDATE {table} SET col1 = :param1, col2 = :param2 WHERE id = {this->id}
     *
     * ⚠️ Значение $this->id подставляется напрямую в SQL. Убедитесь, что оно
     * содержит только целое число.
     *
     * @param array $arr Данные для обновления [колонка => новое_значение]
     *
     * @return void
     */
    public function updateByArr($arr) {
        $columns2params = [];
        $params2values = [];
        $index = 1;
        foreach ($arr as $column => $value) {
            $param = ':param' . $index;
            $columns2params[] = $column . ' = ' . $param;
            $params2values[$param] = $value;
            $index++;
        }
        $sql = 'UPDATE ' . static::getTableName() . ' SET ' . implode(', ', $columns2params) . ' WHERE id = ' . $this->id;
        $db = Application::$app->db;
        $db->query($sql, $params2values);
    }

    /**
     * Получает все записи, соответствующие условиям из массива.
     *
     * Формирует запрос:
     *   SELECT * FROM {table} WHERE col1 = :param1 AND col2 = :param2
     *
     * @param array $arr Условия выборки [колонка => значение]
     * @param bool $use_cache Включить ли кэширование
     *
     * @return array<object>|null Массив объектов или null
     *
     * @throws BaseException Если запрос завершился ошибкой
     */
    public static function getAllByArr(array $arr, $use_cache = false): ?array {
        $columns2params = [];
        $params2values = [];
        $index = 1;
        foreach ($arr as $column => $value) {
            $param = ':param' . $index;
            $columns2params[] = $column . ' = ' . $param;
            $params2values[$param] = $value;
            $index++;
        }
        $sql = 'SELECT * FROM ' . static::getTableName() . ' WHERE '.implode(' AND ', $columns2params).';';
        $db = Application::$app->db;
        return $db->fetchAll($sql, $params2values, static::class, $use_cache);
    }

    /**
     * Возвращает общее количество записей в таблице.
     *
     * @param bool $use_cache Включить ли кэширование
     *
     * @return int|null Количество записей или null при ошибке
     */
    public static function getCountTotal($use_cache = false): ?int {
        $sql = 'SELECT COUNT(*) as cnt FROM ' . static::getTableName() . ';';
        $db = Application::$app->db;
        $res = $db->fetch($sql, [], \stdClass::class, $use_cache);
        return $res->cnt;
    }

    /**
     * Возвращает количество записей, соответствующих условиям.
     *
     * @param array $arr Условия фильтрации [колонка => значение]
     * @param bool $use_cache Включить ли кэширование
     *
     * @return int|null Количество записей или null при ошибке
     */
    public static function getCountByArr(array $arr, $use_cache = false): ?int {
        $columns2params = [];
        $params2values = [];
        $index = 1;
        foreach ($arr as $column => $value) {
            $param = ':param' . $index;
            $columns2params[] = $column . ' = ' . $param;
            $params2values[$param] = $value;
            $index++;
        }
        $sql = 'SELECT COUNT(*) as cnt FROM ' . static::getTableName() . ' WHERE '.implode(' AND ', $columns2params).';';
        $db = Application::$app->db;
        $res = $db->fetch($sql, $params2values, \stdClass::class, $use_cache);
        return $res->cnt;
    }

    /**
     * Получает первую запись, соответствующую условиям из массива.
     *
     * @param array $arr Условия выборки [колонка => значение]
     * @param bool $use_cache Включить ли кэширование
     *
     * @return static|null Объект текущего класса или null если не найдено
     *
     * @throws BaseException Если запрос завершился ошибкой
     */
    public static function getOneByArr(array $arr, $use_cache = false): ?self {
        $columns2params = [];
        $params2values = [];
        $index = 1;
        foreach ($arr as $column => $value) {
            $param = ':param' . $index;
            $columns2params[] = $column . ' = ' . $param;
            $params2values[$param] = $value;
            $index++;
        }
        $sql = 'SELECT * FROM ' . static::getTableName() . ' WHERE '.implode(' AND ', $columns2params).';';
        $db = Application::$app->db;
        return $db->fetch($sql, $params2values, static::class, $use_cache);
    }

    /**
     * Получает записи по условиям с пагинацией и сортировкой по id.
     *
     * @param array $arr Условия выборки
     * @param int $limit Лимит записей
     * @param int $offset Смещение
     * @param string $sort Направление сортировки: 'ASC' или 'DESC'
     * @param bool $use_cache Включить ли кэширование
     *
     * @return array<object>|null Массив объектов или null
     */
    public static function getLimitOffsetByArrSortId(array $arr, int $limit, int $offset, string $sort = 'DESC', $use_cache = false): ?array {
        $columns2params = [];
        $params2values = [];
        $index = 1;
        $sort = $sort == 'DESC' ? 'DESC' : 'ASC';
        foreach ($arr as $column => $value) {
            $param = ':param' . $index;
            $columns2params[] = $column . ' = ' . $param;
            $params2values[$param] = $value;
            $index++;
        }
        $sql = 'SELECT * FROM ' . static::getTableName() . ' WHERE '.implode(' AND ', $columns2params).' ORDER BY id '.$sort.' LIMIT '.$limit.' OFFSET '.$offset.';';
        $db = Application::$app->db;
        return $db->fetchAll($sql, $params2values, static::class, $use_cache);
    }

    /**
     * Получает все записи по условиям с сортировкой по id.
     *
     * @param array $arr Условия выборки
     * @param string $sort Направление сортировки
     * @param bool $use_cache Включить ли кэширование
     *
     * @return array<object>|null Массив объектов или null
     */
    public static function getAllByArrSortId(array $arr, string $sort = 'DESC', $use_cache = false): ?array {
        $columns2params = [];
        $params2values = [];
        $index = 1;
        $sort = $sort == 'DESC' ? 'DESC' : 'ASC';
        foreach ($arr as $column => $value) {
            $param = ':param' . $index;
            $columns2params[] = $column . ' = ' . $param;
            $params2values[$param] = $value;
            $index++;
        }
        $sql = 'SELECT * FROM ' . static::getTableName() . ' WHERE '.implode(' AND ', $columns2params).' ORDER BY id '.$sort.';';
        $db = Application::$app->db;
        return $db->fetchAll($sql, $params2values, static::class, $use_cache);
    }

    /**
     * Получает первую запись по условиям с сортировкой по id.
     *
     * @param array $arr Условия выборки
     * @param string $sort Направление сортировки
     * @param bool $use_cache Включить ли кэширование
     *
     * @return static|null Объект текущего класса или null
     */
    public static function getOneBySortId(array $arr, string $sort = 'DESC', $use_cache = false): ?self {
        $columns2params = [];
        $params2values = [];
        $index = 1;
        $sort = $sort == 'DESC' ? 'DESC' : 'ASC';
        foreach ($arr as $column => $value) {
            $param = ':param' . $index;
            $columns2params[] = $column . ' = ' . $param;
            $params2values[$param] = $value;
            $index++;
        }
        $sql = 'SELECT * FROM ' . static::getTableName() . ' WHERE '.implode(' AND ', $columns2params).' ORDER BY id '.$sort.';';
        $db = Application::$app->db;
        return $db->fetch($sql, $params2values, static::class, $use_cache);
    }
}