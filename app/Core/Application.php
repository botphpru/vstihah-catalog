<?php

namespace App\Core;

use App\Core\Exceptions\LogicException;
use App\Core\Exceptions\NotFoundException;

/**
 * Центральный класс приложения (Service Locator / Container).
 *
 * Отвечает за инициализацию ядра системы, управление зависимостями
 * и запуск процесса обработки запроса.
 *
 * Создается в единственной точке входа (например, index.php) или в cron-скриптах.
 * Хранит статическую ссылку на себя для глобального доступа через функцию app().
 *
 * @package App\Core
 */
class Application
{
    /** @var self Статическая ссылка на единственный экземпляр приложения */
    public static Application $app;

    /** @var Request Объект текущего HTTP-запроса */
    public Request $request;

    /** @var Router Компонент маршрутизации */
    public Router $router;

    /** @var DB Компонент работы с базой данных */
    public DB $db;

    /** @var Auth Компонент аутентификации и авторизации */
    public Auth $auth;

    /** @var View Компонент рендеринга представлений */
    public View $view;

    /** @var Cache Компонент кэширования */
    public Cache $cache;

    /**
     * Конструктор: инициализация ядра приложения.
     *
     * Выполняет последовательную настройку окружения:
     * 1. Регистрирует текущий экземпляр в self::$app
     * 2. Запускает сессию (если не была запущена ранее)
     * 3. Генерирует CSRF-токен (если отсутствует в $_SESSION)
     * 4. Создает и присваивает основные сервисы ядра:
     *    - Request, Router, DB, Auth, View, Cache
     *
     * Примечание:
     * - Для DB используется константа DB (из init.php)
     * - Для View используется константа VIEWS
     *
     * @return void
     */
    public function __construct()
    {
        self::$app = $this;
        // Стартуем сессию
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Генерируем CSRF-токен, если его ещё нет
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $this->request = new Request();
        $this->router = new Router($this->request);
        $this->db = new DB(DB); // Конфиг из init.php
        $this->auth = new Auth();
        $this->view = new View(VIEWS);
        $this->cache = new Cache();
    }

    /**
     * Запускает обработку текущего запроса.
     *
     * Алгоритм работы:
     * 1. Подключает файл маршрутов (config/routes.php)
     * 2. Запрашивает у Router подходящий маршрут через resolve()
     * 3. Если маршрут не найден (null) — выбрасывает NotFoundException
     * 4. Если найден — вызывает обработчик через callHandler()
     *
     * ⚠️ Не вызывается в cron-скриптах, где приложение используется только для доступа к сервисам.
     *
     * @return void
     *
     * @throws NotFoundException Если маршрут для текущего запроса не определен
     */
    public function run(): void
    {
        // Загружаем маршруты
        require_once ROOT . '/config/routes.php';

        // Ищем подходящий маршрут
        $route = $this->router->resolve();

        if ($route === null) {
            throw new NotFoundException("Route not found");
        }

        // Вызываем обработчик
        $this->callHandler($route['handler'], $route['params']);
    }

    /**
     * Вызывает обработчик маршрута (контроллер и метод).
     *
     * Ожидаемый формат $handler: массив [ $controllerClass, $method ]
     *
     * Логика выполнения:
     * 1. Проверяет, что $handler — массив из 2 элементов
     * 2. Проверяет существование класса контроллера
     * 3. Создает экземпляр контроллера, передавая $this->view и $this->request
     * 4. Проверяет существование метода в контроллере
     * 5. Вызывает метод, передавая $params через call_user_func_array
     *
     * @param mixed $handler Обработчик маршрута (ожидается array)
     * @param array $params Параметры для передачи в метод контроллера
     *
     * @return void
     *
     * @throws LogicException Если $handler имеет неверный формат,
     *                        или класс контроллера не найден,
     *                        или метод в контроллере не существует
     */
    private function callHandler($handler, array $params = []): void
    {
        if (is_array($handler) && count($handler) === 2) {
            [$controllerClass, $method] = $handler;

            if (class_exists($controllerClass)) {
                $controller = new $controllerClass($this->view, $this->request);

                if (method_exists($controller, $method)) {
                    // Передаем параметры в метод
                    call_user_func_array([$controller, $method], $params);
                    return;
                }
            }
        }

        throw new LogicException("Controller or method not found");
    }
}