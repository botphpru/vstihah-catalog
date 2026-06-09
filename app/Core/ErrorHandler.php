<?php

namespace App\Core;
/**
 * Глобальный обработчик необработанных исключений и ошибок.
 *
 * Предназначен для установки в единую точку входа (например, через set_exception_handler)
 * или использования в блоках catch контроллеров.
 *
 * Функционал:
 * - Логирование деталей ошибки в файл, имя которого соответствует классу исключения
 * - Сбор диагностической информации: версия PHP, режим отладки, использование памяти, данные запроса
 * - Дифференцированный вывод ошибки:
 *   • В режиме отладки (DEBUG = true или ?test в URL) — подробная техническая информация в браузере
 *   • В продакшене — пользовательское сообщение через ErrorController
 *
 * @package App\Core
 */
class ErrorHandler
{
    /**
     * Обрабатывает необработанное исключение или ошибку.
     *
     * Алгоритм работы:
     * 1. Создаёт экземпляр Log с именем, соответствующим короткому имени класса исключения
     * 2. Заполняет лог:
     *    - Текст исключения и стек вызовов (через addException)
     *    - Данные текущего запроса (через addRequestData)
     *    - Системную информацию: PHP_VERSION, DEBUG_MODE, MEMORY_USAGE
     * 3. Сохраняет лог на диск
     * 4. Выводит ошибку пользователю:
     *    - Если включён DEBUG или в $_GET есть параметр 'test' — показывает детальную отладочную информацию
     *    - Иначе — отображает пользовательскую страницу ошибки через ErrorController
     *
     * @param \Throwable $e Перехваченное исключение или ошибка
     *
     * @return void
     */
    public static function handleException(\Throwable $e): void
    {
        // Создаем лог с именем класса исключения
        $logger = new Log(self::getExceptionName($e));

        // Собираем максимально полную информацию
        $logger
            ->addException($e)
            ->addRequestData()
            ->addText("PHP_VERSION: " . PHP_VERSION)
            ->addText("DEBUG_MODE: " . (DEBUG ? 'ON' : 'OFF'))
            ->addText("MEMORY_USAGE: " . memory_get_usage(true) . " bytes")
            ->save();

        // Показываем ошибку пользователю
        if (DEBUG || isset($_GET['test'])) {
            self::displayDebugError($e, $logger);
        } else {
            self::displayUserError($e);
        }
    }
    /**
     * Извлекает короткое имя класса исключения (без пространства имён).
     *
     * Пример: для \App\Core\Exceptions\NotFoundException вернёт "NotFoundException".
     * Используется для формирования имени файла лога.
     *
     * @param \Throwable $e Исключение для анализа
     *
     * @return string Короткое имя класса
     */
    private static function getExceptionName(\Throwable $e): string
    {
        $class = get_class($e);
        $parts = explode('\\', $class);
        return end($parts);
    }
    /**
     * Отображает пользовательскую страницу ошибки.
     *
     * Создаёт экземпляр ErrorController и вызывает у него errorPage() с параметрами:
     * - Для NotFoundException: тип 'NotFoundException', сообщение 'Страница не найдена'
     * - Для остальных исключений: тип 'SystemException', сообщение 'Внутренняя ошибка сервера'
     *
     * @param \Throwable $e Исключение, которое обрабатывается
     *
     * @return void
     */
    private static function displayUserError(\Throwable $e): void
    {
        $controller = new \App\Controllers\ErrorController(Application::$app->view, Application::$app->request);

        if ($e instanceof \App\Core\Exceptions\NotFoundException) {
            $controller->errorPage('NotFoundException', 'Страница не найдена');
        } else {
            $controller->errorPage('SystemException', 'Внутренняя ошибка сервера');
        }
    }
    /**
     * Отображает детальную отладочную информацию об ошибке в браузере.
     *
     * Выводит HTML-блок с информацией:
     * - Класс исключения и сообщение
     * - Файл и строка, где произошло исключение
     * - Код ошибки
     * - Текущий URI запроса
     * - Стек вызовов (в сворачиваемом блоке <details>)
     * - Содержимое лога через $logger->viewText() (в сворачиваемом блоке)
     *
     * ⚠️ Выводит HTML напрямую в выходной буфер. Не используйте в продакшене без проверки режима.
     *
     * @param \Throwable $e Исключение для отображения
     * @param Log $logger Экземпляр логгера с уже записанными данными
     *
     * @return void
     */
    private static function displayDebugError(\Throwable $e, Log $logger): void
    {
        echo '<div style="background: #f8d7da; color: #721c24; padding: 20px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px;">';
        echo '<h2>DEBUG MODE: ' . get_class($e) . '</h2>';
        echo '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p><strong>File:</strong> ' . $e->getFile() . ':' . $e->getLine() . '</p>';
        echo '<p><strong>Code:</strong> ' . $e->getCode() . '</p>';
        echo '<p><strong>URI:</strong> ' . ($_SERVER['REQUEST_URI'] ?? '') . '</p>';

        echo '<details style="margin-top: 15px;">';
        echo '<summary><strong>Trace:</strong></summary>';
        echo '<pre style="background: white; padding: 10px; border-radius: 3px; overflow: auto;">';
        echo htmlspecialchars($e->getTraceAsString());
        echo '</pre>';
        echo '</details>';

        echo '<details style="margin-top: 15px;">';
        echo '<summary><strong>Log Content:</strong></summary>';
        $logger->viewText();
        echo '</details>';

        echo '</div>';

    }
}