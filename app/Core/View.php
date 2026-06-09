<?php

namespace App\Core;

use App\Core\Exceptions\LogicException;

/**
 * Компонент рендеринга представлений (шаблонов).
 *
 * Отвечает за:
 * - Загрузку и выполнение PHP-шаблонов с передачей данных
 * - Поддержку системы макетов (layouts) для обёртки контента
 * - Рендеринг отдельных блоков без макета (для AJAX или включений)
 * - Вспомогательные методы для безопасного вывода и генерации путей
 * - Обработку кэширования на уровне браузера (304 Not Modified)
 *
 * @package App\Core
 */
class View
{
    /** @var string Базовый путь к директории с шаблонами */
    private string $viewsPath;

    /** @var string Имя текущего макета (по умолчанию 'default') */
    private string $layout = 'default';

    /** @var array Данные, передаваемые в шаблоны */
    private array $data = [];

    /**
     * Конструктор системы представлений.
     *
     * @param string $viewsPath Абсолютный или относительный путь к корневой директории шаблонов
     *
     * @return void
     */
    public function __construct(string $viewsPath)
    {
        $this->viewsPath = rtrim($viewsPath, '/');
    }

    /**
     * Устанавливает имя макета для последующего рендеринга.
     *
     * Макет — это файл-обёртка, в который будет помещён основной контент.
     * Путь к макету: {$viewsPath}/layouts/{$layout}.php
     *
     * Поддерживает цепочку вызовов (Fluent Interface).
     *
     * @param string $layout Имя файла макета без расширения (например, 'admin', 'empty')
     *
     * @return self Возвращает $this для цепочки вызовов
     */
    public function setLayout(string $layout): self
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * Рендерит и возвращает HTML-код отдельного блока (без применения макета).
     *
     * Используется для:
     * - Динамической подгрузки частей страницы через AJAX
     * - Включения одного шаблона в другой
     * - Генерации контента, который будет обработан дополнительно
     *
     * @param string $view Путь к шаблону относительно {$viewsPath}/{$layout}/
     * @param array $data Ассоциативный массив данных, доступных в шаблоне как переменные
     *
     * @return string Готовый HTML-код отрендеренного шаблона
     *
     * @throws LogicException Если файл шаблона не найден
     */
    public function renderBlock(string $view, array $data = []): string
    {
        $this->data = $data;
        return $this->renderView($view);
    }

    /**
     * Рендерит полную страницу с применением макета (если задан).
     *
     * Алгоритм:
     * 1. Сохраняет $data во внутреннее свойство
     * 2. Рендерит основной шаблон через renderView()
     * 3. Если $this->layout не пуст — оборачивает контент в макет через renderLayout()
     * 4. Возвращает итоговый HTML
     *
     * Путь к основному шаблону: {$viewsPath}/{$layout}/{$view}.php
     * Путь к макету: {$viewsPath}/layouts/{$layout}.php
     *
     * @param string $view Путь к шаблону страницы
     * @param array $data Данные для передачи в шаблон
     *
     * @return string Готовый HTML-код страницы (контент + макет)
     */
    public function render(string $view, array $data = []): string
    {
        $this->data = $data;

        $content = $this->renderView($view);

        if ($this->layout) {
            return $this->renderLayout($content);
        }

        return $content;
    }

    /**
     * Рендерит файл шаблона и возвращает его содержимое как строку.
     *
     * Формирует путь к файлу: {$viewsPath}/{$layout}/{$view}.php
     *
     * ⚠️ Шаблон выполняется в текущей области видимости:
     * - Все ключи массива $data становятся доступными как локальные переменные
     * - Используется output buffering (ob_start/ob_get_clean)
     *
     * @param string $view Имя шаблона без расширения
     *
     * @return string Отрендеренный HTML-код шаблона
     *
     * @throws LogicException Если файл шаблона не существует
     */
    private function renderView(string $view): string
    {
        $viewFile = $this->viewsPath . '/' . $this->layout . '/'. $view . '.php';

        if (!file_exists($viewFile)) {
            throw new LogicException("View file not found: {$viewFile}");
        }

        return $this->renderFile($viewFile);
    }

    /**
     * Оборачивает контент в файл макета.
     *
     * Формирует путь к макету: {$viewsPath}/layouts/{$layout}.php
     *
     * Передаёт основной контент в макет через переменную $content:
     *   $this->data['content'] = $content;
     *
     * В файле макета можно вывести контент через:
     *   <?= $content ?>
     *
     * @param string $content HTML-код основного контента для вставки в макет
     *
     * @return string Отрендеренный HTML-код макета с контентом
     *
     * @throws \Exception Если файл макета не существует
     */
    private function renderLayout(string $content): string
    {
        $layoutFile = $this->viewsPath . '/layouts/' . $this->layout . '.php';

        if (!file_exists($layoutFile)) {
            throw new \Exception("Layout file not found: {$layoutFile}");
        }

        // Передаем content в layout
        $this->data['content'] = $content;

        return $this->renderFile($layoutFile);
    }

    /**
     * Выполняет PHP-файл шаблона и возвращает его вывод как строку.
     *
     * Механизм работы:
     * 1. extract($this->data) — делает ключи массива доступными как переменные
     * 2. ob_start() — начинает буферизацию вывода
     * 3. include $file — выполняет шаблон, вывод попадает в буфер
     * 4. ob_get_clean() — возвращает содержимое буфера и очищает его
     *
     * ⚠️ Важно:
     * - В шаблоне доступны все ключи $data как переменные: $data['title'] → $title
     * - Шаблон выполняется в контексте этого метода, $this доступен внутри
     *
     * @param string $file Абсолютный путь к файлу шаблона
     *
     * @return string Содержимое, сгенерированное шаблоном
     */
    private function renderFile(string $file): string
    {
        extract($this->data);
        ob_start();
        include $file;
        return ob_get_clean();
    }

    // === Хелперы для использования внутри шаблонов ===

    /**
     * Экранирует строку для безопасного вывода в HTML.
     *
     * Обёртка над htmlspecialchars() с параметрами:
     * - ENT_QUOTES: экранирует и двойные, и одинарные кавычки
     * - UTF-8: кодировка
     *
     * Если передано null — возвращает пустую строку.
     *
     * Пример использования в шаблоне:
     *   <h1><?= $view->e($title) ?></h1>
     *
     * @param string|null $value Строка для экранирования
     *
     * @return string Безопасная для вывода в HTML строка
     */
    public function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }

    /**
     * Формирует путь к статическому файлу в директории /assets/.
     *
     * Автоматически добавляет начальный слеш и префикс '/assets/'.
     * Убирает дублирующий слеш в начале $path если есть.
     *
     * Примеры:
     *   $view->asset('css/style.css') → '/assets/css/style.css'
     *   $view->asset('/js/app.js')    → '/assets/js/app.js'
     *
     * @param string $path Относительный путь к файлу внутри assets
     *
     * @return string Полный путь от корня сайта
     */
    public function asset(string $path): string
    {
        return '/assets/' . ltrim($path, '/');
    }

    /**
     * Возвращает переданный путь без изменений.
     *
     * ⚠️ В текущей реализации метод является заглушкой (pass-through).
     * Может быть расширен в будущем для:
     * - Добавления базового URL
     * - Генерации URL по имени маршрута
     * - Версионирования ссылок
     *
     * @param string $path Исходный путь или URL
     *
     * @return string Тот же путь, без модификаций
     */
    public function url(string $path): string
    {
        return $path;
    }

    /**
     * Отправляет заголовки для проверки кэша браузера (304 Not Modified).
     *
     * Алгоритм:
     * 1. Конвертирует $dt (дата/время) в Unix-timestamp и формат RFC 2822
     * 2. Проверяет заголовок If-Modified-Since из $_SERVER или $_ENV
     * 3. Если клиентская версия не старше серверной:
     *    - Отправляет статус 304
     *    - Завершает выполнение скрипта (exit)
     * 4. Иначе отправляет заголовок Last-Modified с актуальной датой
     *
     * ⚠️ Побочный эффект: может вызвать exit, если контент не изменился.
     *
     * @param string $dt Дата/время последнего изменения в формате, понятном strtotime()
     *
     * @return void
     */
    public function sendHeadersLastUpdate(string $dt): void
    {
        $LastModified_unix = strtotime($dt);
        $LastModified = gmdate("D, d M Y H:i:s \G\M\T", $LastModified_unix);
        $IfModifiedSince = false;

        if (isset($_ENV['HTTP_IF_MODIFIED_SINCE']))
            $IfModifiedSince = strtotime(substr($_ENV['HTTP_IF_MODIFIED_SINCE'], 5));

        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
            $IfModifiedSince = strtotime(substr($_SERVER['HTTP_IF_MODIFIED_SINCE'], 5));

        if ($IfModifiedSince && $IfModifiedSince >= $LastModified_unix) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');
            exit;
        }

        header('Last-Modified: '. $LastModified);
    }
}