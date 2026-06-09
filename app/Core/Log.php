<?php

namespace App\Core;
/**
 * Класс для формирования и сохранения логов.
 *
 * Реализует паттерн Fluent Interface: методы возвращают $this
 * для возможности цепочки вызовов.
 *
 * Логи сохраняются в файлы с именем формата YYYY-MM-DD.txt
 * внутри директории, соответствующей имени лога.
 *
 * @package App\Core
 */
class Log
{
    /** @var string Имя лога (используется для формирования пути к директории) */
    public string $name;

    /** @var string Накопленный текст лога */
    public string $text;

    /** @var string Путь к директории для сохранения файлов лога */
    public string $dir;

    /**
     * Инициализирует новый лог.
     *
     * @param string $name Имя лога (например, имя класса исключения)
     *
     * @return void
     */

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->text = "\n" . date('Y-m-d H:i:s');
        $this->dir = LOGS . '/'.$this->name.'/';
    }
    /**
     * Добавляет информацию об исключении и контексте выполнения в лог.
     *
     * Собирает данные:
     * - Код, сообщение, файл и строка исключения
     * - Данные запроса: URI, User-Agent, IP, Referer, метод запроса
     * - Стек вызовов (trace)
     *
     * Контекст кодируется в JSON с флагами JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE.
     *
     * @param \Throwable $e Исключение для логирования
     *
     * @return self Возвращает $this для цепочки вызовов
     */
    public function addException(\Throwable $e): self
    {
        $context = [
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'referer' => $_SERVER['HTTP_REFERER'] ?? '',
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'trace' => $e->getTraceAsString()
        ];

        $this->addText("EXCEPTION: " . $e->getMessage());
        $this->addText("CONTEXT: " . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        return $this;
    }
    /**
     * Добавляет данные текущего HTTP-запроса в лог.
     *
     * Сохраняет в формате JSON:
     * - $_GET
     * - $_POST
     * - $_COOKIE
     * - $_SESSION (или пустой массив если сессия не начата)
     *
     * ⚠️ Внимание: в лог могут попасть чувствительные данные (пароли, токены),
     * если они передаются через GET/POST. При необходимости фильтруйте данные перед записью.
     *
     * @return self Возвращает $this для цепочки вызовов
     */
    public function addRequestData(): self
    {
        $requestData = [
            'get' => $_GET,
            'post' => $_POST,
            'cookies' => $_COOKIE,
            'session' => $_SESSION ?? []
        ];

        $this->addText("REQUEST_DATA: " . json_encode($requestData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        return $this;
    }
    /**
     * Добавляет произвольный текст в лог.
     *
     * Каждая запись добавляется с новой строки.
     *
     * @param string $text Текст для добавления
     *
     * @return self Возвращает $this для цепочки вызовов
     */
    public function addText(string $text): self
    {
        $this->text .= "\n" . $text;
        return $this;
    }
    /**
     * Добавляет визуальный разделитель (80 дефисов) в лог.
     *
     * Удобно для разделения отдельных записей в файле лога.
     *
     * @return self Возвращает $this для цепочки вызовов
     */
    public function addDivider(): self
    {
        $this->text .= "\n" . str_repeat('-', 80);
        return $this;
    }
    /**
     * Выводит содержимое лога в браузер в формате <pre>.
     *
     * Текст экранируется через htmlspecialchars() для безопасного отображения.
     *
     * ⚠️ Побочный эффект: напрямую выводит HTML в выходной буфер.
     *
     * @return self Возвращает $this для цепочки вызовов
     */
    public function viewText(): self
    {
        echo '<pre>' . htmlspecialchars($this->text) . '</pre>';
        return $this;
    }
    /**
     * Сохраняет накопленный текст лога в файл.
     *
     * Алгоритм:
     * 1. Формирует имя файла: YYYY-MM-DD.txt
     * 2. Создаёт директорию $this->dir с правами 0777, если она не существует
     * 3. Если файл не существует — создаёт его с правами 0666
     * 4. Добавляет разделитель в конец текста
     * 5. Записывает текст в файл с флагами FILE_APPEND | LOCK_EX
     * 6. При ошибке записи — оставляет закомментированный вызов уведомления в Телеграм
     *
     * ⚠️ Побочные эффекты:
     * - Создание директорий и файлов на диске
     * - Изменение прав доступа (chmod)
     * - Блокировка файла на время записи (LOCK_EX)
     *
     * @return void
     */
    public function save(): void
    {
        $filename = date("Y-m-d") . '.txt';
        $filePath = $this->dir . $filename;

        // Создаем директорию, если она не существует
        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0777, true);
        }

        // Проверяем, существует ли файл, и если нет, создаем его с нужными правами
        if (!file_exists($filePath)) {
            file_put_contents($filePath, '');
            chmod($filePath, 0666);
        }

        // Добавляем разделитель в конце
        $this->addDivider();

        // Пытаемся записать данные в файл
        if (!file_put_contents($filePath, $this->text, FILE_APPEND | LOCK_EX)) {
            //todo: уведомления в телеграм: TgBot::sendNotice('Не сохраняется файл лога: ' . $filePath);
        }
    }
}