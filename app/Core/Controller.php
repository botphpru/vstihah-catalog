<?php

namespace App\Core;
/**
 * Базовый абстрактный класс для всех контроллеров приложения.
 *
 * Предоставляет общую функциональность для обработки запросов и рендеринга:
 * - Хранение экземпляров View и Request
 * - Методы для отрисовки полных страниц и отдельных блоков
 * - Утилиты для ответа в формате JSON и редиректов
 * - Механизм установки layout через абстрактный метод
 *
 * Наследуется классами FrontendController и AdminController,
 * которые реализуют setDefaultLayout() под свои нужды.
 *
 * @package App\Core
 * @abstract
 */
abstract class Controller
{
    /** @var View Экземпляр системы рендеринга представлений */
    protected View $view;
    /** @var Request Объект текущего HTTP-запроса */
    protected Request $request;
    /**
     * Общие данные, которые будут переданы во все шаблоны при рендеринге.
     * Можно заполнять в контроллере перед вызовом render().
     *
     * @var array<string, mixed>
     */
    public array $vars = [];

    /**
     * Конструктор базового контроллера.
     *
     * Инициализирует свойства view и request, затем вызывает
     * setDefaultLayout() для установки шаблона-обёрки.
     *
     * ⚠️ setDefaultLayout() — абстрактный метод, поэтому его реализация
     * в дочернем классе выполнится автоматически при создании экземпляра.
     *
     * @param View $view Экземпляр системы рендеринга
     * @param Request $request Объект текущего запроса
     *
     * @return void
     */
    public function __construct(View $view, Request $request)
    {
        $this->view = $view;
        $this->request = $request;
        $this->setDefaultLayout();
    }
    /**
     * Абстрактный метод для установки стандартного layout.
     *
     * Должен быть реализован в каждом дочернем классе
     * (например, 'default' для публичной части, 'admin' для панели).
     *
     * Вызывается автоматически в конструкторе.
     *
     * @return void
     */
    abstract protected function setDefaultLayout(): void;

    /**
     * Рендерит и возвращает HTML-код отдельного блока (без полного layout).
     *
     * Используется для динамической подгрузки частей страницы,
     * например, через AJAX или для включения в другой шаблон.
     *
     * @param string $view Путь к шаблону блока относительно директории views
     * @param array $data Данные для передачи в шаблон
     *
     * @return string Готовый HTML-код блока
     *
     * @throws Exceptions\LogicException Если шаблон не найден или ошибка рендеринга
     */
    protected function renderBlock(string $view, array $data = [])
    {
        return $this->view->renderBlock($view, $data);
    }
    /**
     * Рендерит полную страницу и выводит результат в ответ.
     *
     * Автоматически объединяет переданные $data с $this->vars,
     * затем передаёт в View::render() и выводит результат через echo.
     *
     * @param string $view Путь к шаблону страницы относительно директории views
     * @param array $data Данные для передачи в шаблон
     *
     * @return void Результат выводится напрямую в выходной буфер
     */
    protected function render(string $view, array $data = []): void
    {
        $data = array_merge($data, $this->vars);
        echo $this->view->render($view, $data);
    }
    /**
     * Устанавливает имя layout-шаблона для текущего запроса.
     *
     * Позволяет переопределить стандартный layout, установленный
     * в setDefaultLayout(), если это требуется для конкретного действия.
     *
     * @param string $layout Имя файла layout без расширения (например, 'empty', 'modal')
     *
     * @return void
     */
    protected function setLayout(string $layout): void
    {
        $this->view->setLayout($layout);
    }
    /**
     * Отправляет ответ в формате JSON и завершает выполнение скрипта.
     *
     * Действия:
     * 1. Устанавливает HTTP-код ответа (по умолчанию 200)
     * 2. Устанавливает заголовок Content-Type: application/json
     * 3. Выводит $data в формате JSON с поддержкой кириллицы (JSON_UNESCAPED_UNICODE)
     * 4. Вызывает exit — дальнейший код не выполнится
     *
     * @param array $data Массив данных для кодирования в JSON
     * @param int $statusCode HTTP-код ответа (по умолчанию 200)
     *
     * @return void Скрипт завершается после вывода
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    /**
     * Выполняет редирект по указанному URL и завершает выполнение скрипта.
     *
     * Действия:
     * 1. Устанавливает HTTP-код ответа (по умолчанию 302 — временный редирект)
     * 2. Отправляет заголовок Location с целевым адресом
     * 3. Вызывает exit — дальнейший код не выполнится
     *
     * ⚠️ Важно: этот метод прерывает выполнение, поэтому код после него не достигнется.
     *
     * @param string $url Целевой URL для редиректа (относительный или абсолютный)
     * @param int $statusCode HTTP-код редиректа (301, 302, 303, 307 и т.д.)
     *
     * @return void Скрипт завершается после отправки заголовка
     */
    protected function redirect(string $url, int $statusCode = 302): void
    {
        http_response_code($statusCode);
        header('Location: ' . $url);
        exit;
    }
}