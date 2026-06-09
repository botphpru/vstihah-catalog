<?php

namespace App\Core;

/**
 * Обёртка над данными HTTP-запроса.
 *
 * Предоставляет удобный и типизированный доступ к:
 * - URI, пути, параметрам запроса (GET/POST)
 * - HTTP-методу, заголовкам сервера, куки
 * - Вспомогательные методы для валидации и нормализации
 *
 * ⚠️ Конструктор может выполнить редирект и завершить выполнение скрипта,
 * если исходный URI требует нормализации.
 *
 * @package App\Core
 */
class Request
{
    /** @var string Оригинальный URI из $_SERVER['REQUEST_URI'] */
    private string $uri;

    /** @var string Нормализованный URI (без дублирующих слешей, без конечного слеша) */
    private string $normalizedUri;

    /** @var string Только путь из URI (без query-строки), используется для роутинга */
    private string $path;

    /** @var array Параметры из $_GET */
    private array $query;

    /** @var array Параметры из $_POST */
    private array $request;

    /** @var array Данные из $_SERVER */
    private array $server;

    /** @var array Данные из $_COOKIE */
    private array $cookies;

    /** @var string|null Первый сегмент пути (например, 'admin' из '/admin/users') */
    private string $firstDir;

    /**
     * Инициализирует объект запроса данными из суперглобальных массивов.
     *
     * Алгоритм:
     * 1. Получает сырой URI из $_SERVER['REQUEST_URI'] (или '/' по умолчанию)
     * 2. Парсит URI на путь и query-строку через parse_url()
     * 3. Нормализует путь через normalizeUri():
     *    - Убирает дублирующие слеши (// → /)
     *    - Удаляет конечный слеш, кроме корня '/'
     *    - Гарантирует начальный слеш
     * 4. Если путь изменился после нормализации — выполняет 301-редирект на нормализованный URI
     *    с сохранением query-строки и завершает скрипт (exit)
     * 5. Сохраняет исходный и нормализованный URI, извлекает первый сегмент пути
     * 6. Копирует $_GET, $_POST, $_SERVER, $_COOKIE в приватные свойства
     *
     * ⚠️ Побочный эффект: может выполнить header('Location: ...') + exit,
     * поэтому код после создания экземпляра этого класса может не выполниться.
     *
     * @return void
     */
    public function __construct()
    {
        $rawUri = $_SERVER['REQUEST_URI'] ?? '/';

        // Парсим URI для получения пути и query-строки
        $parsedUri = parse_url($rawUri);
        $path = $parsedUri['path'] ?? '/';

        $this->normalizedUri = $this->normalizeUri($path);

        // Редирект если URI изменился (только для пути)
        if ($path !== $this->normalizedUri) {
            $this->redirectPermanently($this->normalizedUri . (isset($parsedUri['query']) ? '?' . $parsedUri['query'] : ''));
        }

        $this->uri = $rawUri; // Оригинальный URI
        $this->path = $this->normalizedUri; // Только путь для роутинга

        $pathParts = explode('/', trim($this->path, '/'));
        $this->firstDir = $pathParts[0];

        $this->query = $_GET;
        $this->request = $_POST;
        $this->server = $_SERVER;
        $this->cookies = $_COOKIE;
    }

    /**
     * Возвращает первый сегмент пути из нормализованного URI.
     *
     * Примеры:
     * - '/admin/users' → 'admin'
     * - '/blog/post-123' → 'blog'
     * - '/' → '' (пустая строка)
     *
     * @return string|null Первый сегмент пути или null если путь пуст
     */
    public function getFirstDir(): ?string
    {
        return $this->firstDir;
    }

    /**
     * Возвращает оригинальный URI запроса (как был получен из $_SERVER).
     *
     * @return string Полный URI с query-строкой, без нормализации
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * Возвращает нормализованный путь без query-строки.
     *
     * Используется для маршрутизации: путь очищен от дублирующих слешей,
     * не имеет конечного слеша (кроме корня), всегда начинается с '/'.
     *
     * @return string Нормализованный путь
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Возвращает HTTP-метод текущего запроса.
     *
     * @return string Метод запроса (например, 'GET', 'POST') или 'GET' по умолчанию
     */
    public function getMethod(): string
    {
        return $this->server['REQUEST_METHOD'] ?? 'GET';
    }

    /**
     * Получает значение параметра по ключу из GET или POST.
     *
     * Порядок поиска:
     * 1. $_GET[$key]
     * 2. $_POST[$key]
     * 3. $default если ключ не найден нигде
     *
     * ⚠️ Параметры GET имеют приоритет над POST при совпадении имён.
     *
     * @param string $key Имя параметра
     * @param mixed $default Значение по умолчанию если параметр не найден
     *
     * @return mixed Значение параметра или $default
     */
    public function get(string $key, $default = null)
    {
        return $this->query[$key] ?? $this->request[$key] ?? $default;
    }

    /**
     * Возвращает все параметры запроса, объединяя GET и POST.
     *
     * ⚠️ При совпадении имён ключей значения из GET перезапишут значения из POST
     * (так как array_merge($this->query, $this->request) ставит $query первым).
     *
     * @return array Ассоциативный массив всех параметров
     */
    public function all(): array
    {
        return array_merge($this->query, $this->request);
    }

    /**
     * Получает данные из $_GET.
     *
     * - Если $key не указан — возвращает весь массив $_GET
     * - Если $key указан — возвращает значение или $default если ключ отсутствует
     *
     * @param string|null $key Имя параметра (опционально)
     * @param mixed $default Значение по умолчанию
     *
     * @return mixed|array Значение параметра, массив всех GET-параметров или $default
     */
    public function query(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->query;
        }
        return $this->query[$key] ?? $default;
    }

    /**
     * Получает данные из $_POST.
     *
     * - Если $key не указан — возвращает весь массив $_POST
     * - Если $key указан — возвращает значение или $default если ключ отсутствует
     *
     * @param string|null $key Имя параметра (опционально)
     * @param mixed $default Значение по умолчанию
     *
     * @return mixed|array Значение параметра, массив всех POST-параметров или $default
     */
    public function post(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->request;
        }
        return $this->request[$key] ?? $default;
    }

    /**
     * Проверяет, является ли текущий запрос методом GET.
     *
     * @return bool true если метод запроса — 'GET'
     */
    public function isGet(): bool
    {
        return $this->getMethod() === 'GET';
    }

    /**
     * Проверяет, является ли текущий запрос методом POST.
     *
     * @return bool true если метод запроса — 'POST'
     */
    public function isPost(): bool
    {
        return $this->getMethod() === 'POST';
    }

    /**
     * Нормализует путь URI.
     *
     * Правила нормализации:
     * 1. Заменяет множественные слеши на один (// → /)
     * 2. Удаляет конечный слеш, если путь не является корнем '/'
     * 3. Добавляет начальный слеш, если он отсутствует и путь не пуст
     *
     * Примеры:
     * - '//admin//users//' → '/admin/users'
     * - 'blog/post' → '/blog/post'
     * - '/' → '/'
     *
     * @param string $uri Исходный путь
     *
     * @return string Нормализованный путь
     */
    private function normalizeUri(string $uri): string
    {
        // Убираем множественные слеши (// -> /)
        $normalizedPath = preg_replace('#/+#', '/', $uri);

        // Убираем trailing slash, кроме корня
        if ($normalizedPath !== '/' && substr($normalizedPath, -1) === '/') {
            $normalizedPath = rtrim($normalizedPath, '/');
        }

        // Ensure leading slash for non-empty paths
        if ($normalizedPath !== '/' && $normalizedPath[0] !== '/') {
            $normalizedPath = '/' . $normalizedPath;
        }

        return $normalizedPath;
    }

    /**
     * Выполняет постоянный редирект (301) на указанный URI.
     *
     * Формирует полный URL:
     * - Протокол: https если $_SERVER['HTTPS'] установлен и не 'off', иначе http
     * - Хост: $_SERVER['HTTP_HOST'] или константа HOME_DOMAIN если не задан
     * - Путь: переданный $newUri
     *
     * Отправляет заголовки:
     * - HTTP/1.1 301 Moved Permanently
     * - Location: {полный URL}
     *
     * Завершает выполнение скрипта через exit.
     *
     * ⚠️ Побочный эффект: скрипт завершается, дальнейший код не выполнится.
     *
     * @param string $newUri Целевой URI для редиректа
     *
     * @return void
     */
    private function redirectPermanently(string $newUri): void
    {
        // Полный URL с протоколом и доменом
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? HOME_DOMAIN;
        $fullUrl = $protocol . '://' . $host . $newUri;

        // 301 Permanent Redirect
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $fullUrl);

        // Завершаем выполнение
        exit;
    }

    /**
     * Получает номер страницы для пагинации из параметра $_GET['page'].
     *
     * Правила валидации:
     * 1. Если параметр отсутствует или не состоит только из цифр — возвращается 1
     * 2. Если значение меньше 1 — возвращается 1
     * 3. Иначе значение приводится к целому числу и возвращается
     *
     * Примеры:
     * - ?page=5 → 5
     * - ?page=abc → 1
     * - ?page=-3 → 1
     * - (нет параметра) → 1
     *
     * @return int Номер страницы (всегда >= 1)
     */
    public function getNumberPage(): int
    {
        $page = $_GET['page'] ?? '1';
        // Проверяем, что page состоит только из цифр
        if (!preg_match("/^([0-9])+$/", $page)) {
            $page = 1;
        } else {
            $page = (int)$page;
            // Убедимся, что страница не меньше 1
            if ($page < 1) {
                $page = 1;
            }
        }
        return $page;
    }
}