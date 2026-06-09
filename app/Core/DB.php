<?php

namespace App\Core;

use PDO;
use PDOException;
use stdClass;
use App\Core\Exceptions\BaseException;
/**
 * Обёртка над PDO для работы с базой данных.
 *
 * Предоставляет удобный интерфейс для выполнения запросов с дополнительными возможностями:
 * - Автоматическое кэширование результатов выборки (опционально)
 * - Встроенная обработка ошибок с преобразованием в BaseException
 * - Helper-методы для типовых операций: insert, update, delete, count
 * - Поддержка транзакций, в том числе через callback
 *
 * @package App\Core
 */
class DB
{
    private PDO $connection;

    /**
     * Инициализирует соединение с базой данных через PDO.
     *
     * Ожидаемая структура $config:
     * - host: хост БД
     * - dbname: имя базы данных
     * - charset: кодировка соединения
     * - username: логин
     * - password: пароль
     * - options: массив опций для PDO (например, PDO::ATTR_ERRMODE)
     *
     * При ошибке подключения перехватывает PDOException и выбрасывает BaseException
     * с сохранением оригинального кода и сообщения.
     *
     * @param array $config Параметры подключения к БД
     *
     * @return void
     *
     * @throws BaseException Если не удалось установить соединение
     */
    public function __construct(array $config)
    {
        try {
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";

            $this->connection = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options']
            );

        } catch (PDOException $e) {
            throw new BaseException(
                "Database connection failed: " . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /**
     * Выполняет подготовленный SQL-запрос с параметрами.
     *
     * Автоматически подготавливает запрос, привязывает параметры
     * и выполняет его. Все ошибки PDO перехватываются и преобразуются
     * в BaseException с добавлением текста самого запроса для отладки.
     *
     * @param string $sql SQL-запрос с именованными плейсхолдерами (:param)
     * @param array $params Ассоциативный массив параметров для привязки
     *
     * @return \PDOStatement Выполненный стейтмент
     *
     * @throws BaseException Если выполнение запроса завершилось ошибкой
     */
    public function query(string $sql, array $params = [])
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            // Приводим код к int, если это строка
            $code = $e->getCode();
            if (is_string($code) && is_numeric($code)) {
                $code = (int) $code;
            } elseif (is_string($code)) {
                $code = 0; // или другой код по умолчанию
            }

            throw new BaseException(
                "Database query failed: " . $e->getMessage() . " | SQL: " . $sql,
                $code,
                $e
            );
        }
    }

    /**
     * Получает одну запись из результата запроса.
     *
     * Поддерживает:
     * - Кэширование результата (при $useCache = true)
     * - Возврат данных как объект указанного класса или stdClass
     *
     * Логика кэширования:
     * 1. Генерируется ключ через generateCacheKey()
     * 2. Проверяется наличие в кэше через Application::$app->cache->get()
     * 3. При промахе — выполняется запрос, результат сохраняется в кэш
     *
     * @param string $sql SQL-запрос с плейсхолдерами
     * @param array $params Параметры для привязки
     * @param string|null $className Имя класса для маппинга результата (или null для stdClass)
     * @param bool $useCache Включить ли кэширование этого запроса
     * @param int $cacheSeconds Время жизни кэша в секундах (по умолчанию используется константа CACHE_SECONDS)
     *
     * @return object|null Объект с данными или null если записей нет
     *
     * @throws BaseException Если запрос завершился ошибкой
     */
    public function fetch(string $sql, array $params = [], ?string $className = null, bool $useCache = false, int $cacheSeconds = CACHE_SECONDS): ?object
    {
        $cacheKey = null;

        if ($useCache) {
            $cacheKey = $this->generateCacheKey($sql, $params, 'fetch');
            $cached = Application::$app->cache->get($cacheKey);

            if ($cached !== null) {
                return $cached;
            }
        }

        $stmt = $this->query($sql, $params);

        if ($className) {
            $stmt->setFetchMode(PDO::FETCH_CLASS, $className);
        } else {
            $stmt->setFetchMode(PDO::FETCH_OBJ);
        }

        $result = $stmt->fetch() ?: null;

        if ($useCache && $result !== null && $cacheKey !== null) {
            Application::$app->cache->set($cacheKey, $result, $cacheSeconds);
        }

        return $result;
    }

    /**
     * Получает все записи из результата запроса.
     *
     * Поддерживает:
     * - Кэширование результата (при $useCache = true)
     * - Возврат данных как массив объектов указанного класса или stdClass
     *
     * Логика кэширования аналогична fetch().
     *
     * @param string $sql SQL-запрос с плейсхолдерами
     * @param array $params Параметры для привязки
     * @param string|null $className Имя класса для маппинга результата (или null для stdClass)
     * @param bool $useCache Включить ли кэширование этого запроса
     * @param int $cacheSeconds Время жизни кэша в секундах
     *
     * @return array<object> Массив объектов с данными (пустой массив если записей нет)
     *
     * @throws BaseException Если запрос завершился ошибкой
     */
    public function fetchAll(string $sql, array $params = [], ?string $className = null, bool $useCache = false, int $cacheSeconds = CACHE_SECONDS): array
    {
        $cacheKey = null;

        if ($useCache) {
            $cacheKey = $this->generateCacheKey($sql, $params, 'fetchAll');
            $cached = Application::$app->cache->get($cacheKey);

            if ($cached !== null) {
                return $cached;
            }
        }

        $stmt = $this->query($sql, $params);

        if ($className) {
            $result = $stmt->fetchAll(PDO::FETCH_CLASS, $className);
        } else {
            $result = $stmt->fetchAll(PDO::FETCH_OBJ);
        }

        if ($useCache && $cacheKey !== null) {
            Application::$app->cache->set($cacheKey, $result, $cacheSeconds);
        }

        return $result;
    }
    /**
     * Генерирует уникальный ключ для кэширования запроса.
     *
     * Ключ формируется на основе:
     * - текста SQL-запроса
     * - массива параметров
     * - типа операции ('fetch' или 'fetchAll')
     *
     * Результат сериализуется и хэшируется через md5.
     *
     * @param string $sql SQL-запрос
     * @param array $params Параметры запроса
     * @param string $type Тип операции выборки
     *
     * @return string MD5-хэш, используемый как ключ в кэше
     */
    private function generateCacheKey(string $sql, array $params, string $type): string
    {
        // Создаем уникальный ключ на основе SQL и параметров
        $keyData = [
            'sql' => $sql,
            'params' => $params,
            'type' => $type
        ];

        return md5(serialize($keyData));
    }

    /**
     * Вставка записи в таблицу
     * @throws BaseException
     */
    public function insert(string $table, array $data): bool
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";

        $stmt = $this->query($sql, $data);
        return $stmt->rowCount() > 0;
    }

    /**
     * Вставка записи с возвратом ID
     * @throws BaseException
     */
    public function insertGetId(string $table, array $data): int
    {
        $this->insert($table, $data);
        return (int)$this->connection->lastInsertId();
    }

    /**
     * Обновление записей
     * @throws BaseException
     */
    public function update(string $table, array $data, array $where): bool
    {
        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = "{$column} = :{$column}";
        }
        $setClause = implode(', ', $setParts);

        $whereParts = [];
        $whereParams = [];
        foreach ($where as $column => $value) {
            $whereKey = "where_{$column}";
            $whereParts[] = "{$column} = :{$whereKey}";
            $whereParams[$whereKey] = $value;
        }
        $whereClause = implode(' AND ', $whereParts);

        $sql = "UPDATE {$table} SET {$setClause} WHERE {$whereClause}";
        $params = array_merge($data, $whereParams);

        $stmt = $this->query($sql, $params);
        return $stmt->rowCount() > 0;
    }

    /**
     * Удаление записей
     * @throws BaseException
     */
    public function delete(string $table, array $where): bool
    {
        $whereParts = [];
        foreach (array_keys($where) as $column) {
            $whereParts[] = "{$column} = :{$column}";
        }
        $whereClause = implode(' AND ', $whereParts);

        $sql = "DELETE FROM {$table} WHERE {$whereClause}";

        $stmt = $this->query($sql, $where);
        return $stmt->rowCount() > 0;
    }

    /**
     * Получение количества записей
     * @throws BaseException
     */
    public function count(string $table, array $where = []): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$table}";

        if (!empty($where)) {
            $whereParts = [];
            foreach (array_keys($where) as $column) {
                $whereParts[] = "{$column} = :{$column}";
            }
            $sql .= " WHERE " . implode(' AND ', $whereParts);
        }

        $result = $this->fetch($sql, $where);
        return (int)($result->count ?? 0);
    }

    /**
     * Проверка существования записи
     * @throws BaseException
     */
    public function exists(string $table, array $where): bool
    {
        return $this->count($table, $where) > 0;
    }

    /**
     * Начинает транзакцию
     */
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Подтверждает транзакцию
     */
    public function commit(): bool
    {
        return $this->connection->commit();
    }

    /**
     * Откатывает транзакцию
     */
    public function rollBack(): bool
    {
        return $this->connection->rollBack();
    }

    /**
     * Получение последнего вставленного ID
     */
    public function lastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }

    /**
     * Выполнение в транзакции
     * @throws BaseException
     */
    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();

        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }
}