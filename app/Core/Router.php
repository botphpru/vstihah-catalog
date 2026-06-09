<?php

namespace App\Core;

/**
 * Компонент маршрутизации HTTP-запросов.
 *
 * Отвечает за:
 * - Регистрацию маршрутов для методов GET и POST
 * - Преобразование паттернов в регулярные выражения
 * - Поиск подходящего маршрута по методу и пути текущего запроса
 * - Извлечение параметров из захваченных групп регулярного выражения
 *
 * Паттерны маршрутов принимаются в виде регулярных выражений
 * и автоматически оборачиваются в якоря начала/конца строки.
 *
 * @package App\Core
 */
class Router
{
    /**
     * Список зарегистрированных маршрутов.
     * Каждый элемент содержит: method, pattern, regex, handler
     *
     * @var array<array{method: string, pattern: string, regex: string, handler: mixed}>
     */
    private array $routes = [];

    /** @var Request Объект запроса для получения метода и пути */
    private Request $request;

    /**
     * Конструктор роутера.
     *
     * @param Request $request Объект текущего запроса
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Регистрирует маршрут для метода GET.
     *
     * @param string $pattern Паттерн пути в виде регулярного выражения
     * @param mixed $handler Обработчик маршрута (например, [Класс, метод] или замыкание)
     *
     * @return void
     */
    public function get(string $pattern, $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    /**
     * Регистрирует маршрут для метода POST.
     *
     * @param string $pattern Паттерн пути в виде регулярного выражения
     * @param mixed $handler Обработчик маршрута
     *
     * @return void
     */
    public function post(string $pattern, $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    /**
     * Внутренний метод для добавления маршрута в коллекцию.
     *
     * Преобразует пользовательский паттерн в полное регулярное выражение
     * через convertPatternToRegex() и сохраняет маршрут с метаданными.
     *
     * @param string $method HTTP-метод: 'GET' или 'POST'
     * @param string $pattern Паттерн пути (регулярное выражение)
     * @param mixed $handler Обработчик маршрута
     *
     * @return void
     */
    private function add(string $method, string $pattern, $handler): void
    {
        // Преобразуем ваш синтаксис в регулярное выражение
        $regex = $this->convertPatternToRegex($pattern);

        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern, // оригинальный паттерн
            'regex' => $regex,     // скомпилированное регулярное выражение
            'handler' => $handler
        ];
    }

    /**
     * Преобразует пользовательский паттерн в готовое регулярное выражение.
     *
     * Добавляет к паттерну:
     * - Якорь начала строки: ^
     * - Якорь конца строки: $
     * - Модификатор: #i (регистронезависимое совпадение)
     *
     * Пример:
     *   Вход: '/blog/([a-z0-9-]+)'
     *   Выход: '#^/blog/([a-z0-9-]+)$#i'
     *
     * ⚠️ Паттерн должен быть валидным регулярным выражением.
     * Метод не экранирует специальные символы — это ответственность вызывающего кода.
     *
     * @param string $pattern Паттерн пути (регулярное выражение без якорей)
     *
     * @return string Полное регулярное выражение с якорями и модификатором
     */
    private function convertPatternToRegex(string $pattern): string
    {
        // Просто добавляем начало и конец, ваш паттерн уже содержит regex
        return '#^' . $pattern . '$#i';
    }

    /**
     * Находит подходящий маршрут для текущего запроса.
     *
     * Алгоритм:
     * 1. Получает метод и путь из объекта Request
     * 2. Перебирает зарегистрированные маршруты в порядке добавления
     * 3. Для каждого маршрута проверяет:
     *    - Совпадение метода (GET/POST)
     *    - Совпадение пути с регулярным выражением маршрута
     * 4. При совпадении:
     *    - Извлекает захваченные группы через preg_match()
     *    - Удаляет полное совпадение (индекс 0) из массива $matches
     *    - Возвращает массив с обработчиком и параметрами
     * 5. Если совпадений нет — возвращает null
     *
     * Пример паттерна и извлечения параметров:
     *   Паттерн: '/user/([0-9]+)/post/([a-z-]+)'
     *   Путь: '/user/42/post/my-first-post'
     *   Результат: ['handler' => ..., 'params' => ['42', 'my-first-post']]
     *
     * @return array{handler: mixed, params: array<string>}|null
     *         Массив с обработчиком и параметрами или null если маршрут не найден
     */
    public function resolve(): ?array
    {
        $method = $this->request->getMethod();
        $path = $this->request->getPath(); // Используем getPath() вместо getUri()

        foreach ($this->routes as $route) {
            if ($route['method'] === $method &&
                preg_match($route['regex'], $path, $matches)) {

                // Убираем полное совпадение (индекс 0)
                array_shift($matches);

                return [
                    'handler' => $route['handler'],
                    'params' => $matches
                ];
            }
        }

        return null;
    }
}