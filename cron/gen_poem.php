<?php
// cron/gen_poem.php
require_once __DIR__ . '/../config/init.php';
set_error_handler('errors_handler');
require_once ROOT . '/vendor/autoload.php';



try {
    $app = new \App\Core\Application();
    require_once HELPERS . '/helpers.php';

    // === Конфигурация ===
    $maxRetries = 2;

    $db = \App\Core\Application::$app->db;

    // === 1. Получаем СЛУЧАЙНУЮ запись из очереди ===
    $sql = "SELECT * FROM generation_queue 
            WHERE generated_count = 0 
            ORDER BY RAND() 
            LIMIT 1";
    $queueItem = $db->fetch($sql, [], \App\Models\GenerationQueue::class);

    if (!$queueItem) {
        $minRow = $db->fetch(
            "SELECT MIN(generated_count) as cnt FROM generation_queue",
            [],
            \stdClass::class
        );
        $minCount = $minRow?->cnt ?? 0;

        $sql = "SELECT * FROM generation_queue 
                WHERE generated_count = :minCount 
                ORDER BY RAND() 
                LIMIT 1";
        $queueItem = $db->fetch($sql, [':minCount' => $minCount], \App\Models\GenerationQueue::class);
    }

    if (!$queueItem) {
        throw new \LogicException("Очередь generation_queue пуста");
    }

    // === 2. Собираем данные для промпта ===
    $promptData = ['quatrains' => $queueItem->quatrains];

    if ($queueItem->event_id) {
        $event = \App\Models\Event::getById($queueItem->event_id);
        $promptData['event'] = $event?->name;
    }
    if ($queueItem->genre_id) {
        $genre = \App\Models\Genre::getById($queueItem->genre_id);
        $promptData['genre'] = $genre?->name;
    }
    if ($queueItem->name_id) {
        $name = \App\Models\Name::getById($queueItem->name_id);
        $promptData['name'] = $name?->name;
    }
    if ($queueItem->recipient_id) {
        $recipient = \App\Models\Recipient::getById($queueItem->recipient_id);
        $promptData['recipient'] = $recipient?->name;
    }

    // === 3. Формируем промпт ===
    $prompt = \App\Services\Prompt::getPromptForGenPoem($promptData);

    // === 4. Запрос к ИИ ===
    $client = new \App\Services\DeepSeekClient(DEEPSEEK_API);
    $poetryOptions = [
        'preset' => 'poetry',  // 🔥 Используем пресет с thinking mode
    ];


    $aiText = null;
    $lastError = null;

    for ($attempt = 1; $attempt <= $maxRetries + 1; $attempt++) {
        try {
            $aiText = $client->getResponseText($prompt, $poetryOptions);

            // Базовые проверки
            if (!$aiText || trim($aiText) === '') {
                throw new \Exception("Пустой ответ от API");
            }

            // Очистка от маркеров кода
            $aiText = preg_replace('/^```[\w]*\s*|\s*```$/m', '', trim($aiText));
            $aiText = trim($aiText);

            // 🔥 НОВОЕ: Удаляем вводные фразы ИИ (если первая строка содержит ":" и за ней идет пустая строка)
            // Паттерн: начало строки (^), любые символы кроме переноса строки до двоеточия,
            // само двоеточие, возможные пробелы, и минимум два переноса строки (пустая строка).
            $aiText = preg_replace('/^[^\n]*:[ \t]*[\r\n]{2,}/u', '', $aiText);
            // На всякий случай еще раз тримим, чтобы убрать лишние пробелы, если они остались
            $aiText = trim($aiText);

            // 🔥 НОВАЯ ВАЛИДАЦИЯ: только русский текст 🔥
            if (!isValidRussianPoem($aiText)) {
                throw new \Exception("Текст содержит недопустимые символы или недостаточно кириллицы");
            }

            // Дополнительная проверка: минимальная длина
            if (mb_strlen($aiText) < 50) {
                throw new \Exception("Текст слишком короткий для стихотворения");
            }

            break; // Успех — выходим из цикла попыток

        } catch (Throwable $e) {
            $lastError = $e->getMessage();
            if ($attempt <= $maxRetries) {
                sleep(2);
                continue;
            }
            // Все попытки исчерпаны — завершаем без сохранения
            throw new \Exception("Генерация отклонена: $lastError");
        }
    }

    // === 5. Сохранение в БД (транзакция) ===
    $db->beginTransaction();

    try {
        // 5.1 Вставляем стих через вашу модель
        \App\Models\Poem::insertByArr([
            'text' => $aiText,
            'quatrains' => $queueItem->quatrains,
        ]);
        $poemId = (int)$db->lastInsertId(); // ⚠️ cast to int!

        // 5.2 Добавляем связи
        if ($queueItem->event_id) {
            \App\Models\RelationPoemEvent::insertByArr([
                'poem_id' => $poemId,
                'event_id' => $queueItem->event_id
            ]);
        }
        if ($queueItem->genre_id) {
            \App\Models\RelationPoemGenre::insertByArr([
                'poem_id' => $poemId,
                'genre_id' => $queueItem->genre_id
            ]);
        }
        if ($queueItem->name_id) {
            \App\Models\RelationPoemName::insertByArr([
                'poem_id' => $poemId,
                'name_id' => $queueItem->name_id
            ]);
        }
        if ($queueItem->recipient_id) {
            \App\Models\RelationPoemRecipient::insertByArr([
                'poem_id' => $poemId,
                'recipient_id' => $queueItem->recipient_id
            ]);
        }

        // 5.3 Обновляем очередь
        $queueItem->updateByArr([
            'generated_count' => $queueItem->generated_count + 1,
            'last_generated_at' => date('Y-m-d H:i:s')
        ]);

        $db->commit();
        $success = true;

    } catch (Throwable $e) {
        $db->rollBack(); // Ваш метод есть
        throw $e;
    }

    // === 6. Вывод результата ===
    $isCli = php_sapi_name() === 'cli';

    if (!$isCli) {
        echo "<!DOCTYPE html><html><head><meta charset='utf-8'><style>
            body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', monospace; padding: 20px; line-height: 1.5; }
            .success { color: #2e7d32; font-weight: bold; }
            .error { color: #c62828; }
            .section { margin: 20px 0; padding: 15px; background: #f5f5f5; border-radius: 8px; border-left: 4px solid #1976d2; }
            .prompt { background: #e3f2fd; border-left-color: #1976d2; }
            .poem { background: #fff8e1; border-left-color: #f9a825; font-style: italic; white-space: pre-wrap; }
            .meta { font-size: 0.9em; color: #666; }
            h2 { margin: 0 0 10px; }
        </style></head><body>";
    }

    echo "<h2>" . ($success ? '✅ Стих успешно сгенерирован!' : '❌ Ошибка') . "</h2>";

    echo "<div class='section meta'>";
    echo "<strong>ID стиха:</strong> $poemId";
    if (!$isCli) {
        echo " | <a href='/poems/$poemId' target='_blank'>Открыть</a>";
    }
    echo "</div>";

    echo "<div class='section prompt'><strong>📝 Промпт:</strong><br>" .
        htmlspecialchars($prompt) . "</div>";

    echo "<div class='section poem'><strong>✨ Результат:</strong><br>" .
        nl2br(htmlspecialchars($aiText)) . "</div>";

    echo "<div class='section meta'><strong>⚙️ Параметры:</strong><br>";
    $sizeLabel = match($queueItem->quatrains) {
        1 => 'короткий (1 четв.)',
        4 => 'средний (4 четв.)',
        8 => 'длинный (8 четв.)',
        default => "{$queueItem->quatrains} четв.",
    };
    echo "Размер: <b>$sizeLabel</b><br>";
    echo "Событие: " . ($promptData['event'] ?? '—') . "<br>";
    echo "Жанр: " . ($promptData['genre'] ?? '—') . "<br>";
    echo "Имя: " . ($promptData['name'] ?? '—') . "<br>";
    echo "Получатель: " . ($promptData['recipient'] ?? '—') . "<br>";
    echo "</div>";

    if (!$isCli) {
        echo "</body></html>";
    }

} catch (Throwable $e) {
    // Откат, если транзакция активна
    $db = \App\Core\Application::$app->db ?? null;
    if ($db) {
        try { $db->rollBack(); } catch (\Throwable) {}
    }

    \App\Core\ErrorHandler::handleException($e);

    // Фоллбэк-вывод, если хендлер не отработал
    if (!headers_sent()) {
        $isCli = php_sapi_name() === 'cli';
        if (!$isCli) {
            http_response_code(500);
            echo "<!DOCTYPE html><html><head><meta charset='utf-8'><style>
                body { font-family: monospace; padding: 20px; background: #fff; }
                .error { color: #c62828; background: #ffebee; padding: 15px; border-radius: 5px; border: 1px solid #ffcdd2; }
                pre { background: #f5f5f5; padding: 10px; overflow-x: auto; font-size: 12px; }
            </style></head><body>";
            echo "<h2>❌ Ошибка генерации</h2>";
            echo "<div class='error'><strong>" . htmlspecialchars($e->getMessage()) . "</strong></div>";
            echo "<details><summary>Stack trace</summary><pre>" .
                htmlspecialchars($e->getTraceAsString()) . "</pre></details>";
            echo "</body></html>";
        } else {
            echo "❌ ERROR: " . $e->getMessage() . "\n";
            echo $e->getTraceAsString() . "\n";
        }
    }
}


/**
 * Валидация текста стиха: только русский язык
 * @param string $text
 * @return bool
 */
function isValidRussianPoem(string $text): bool
{
    // Убираем markdown-блоки кода
    $text = preg_replace('/^```[\w]*\s*|\s*```$/m', '', trim($text));

    // Минимальная длина
    if (mb_strlen($text, 'UTF-8') < 30) {
        return false;
    }

    // Запрещённые символы: CJK-иероглифы и эмодзи
    $forbidden = '/[\x{4E00}-\x{9FFF}\x{3400}-\x{4DBF}\x{3040}-\x{309F}\x{30A0}-\x{30FF}\x{AC00}-\x{D7AF}\x{1F300}-\x{1F9FF}]/u';

    if (preg_match($forbidden, $text)) {
        return false;
    }

    // Считаем соотношение кириллицы ТОЛЬКО среди букв
    $lettersOnly = preg_replace('/[^\p{L}]/u', '', $text);

    if (mb_strlen($lettersOnly, 'UTF-8') < 15) {
        return false;
    }

    $cyrillicCount = preg_match_all('/\p{Cyrillic}/u', $lettersOnly);
    $ratio = $cyrillicCount / mb_strlen($lettersOnly, 'UTF-8');

    // Минимум 80% букв должны быть кириллицей
    return $ratio >= 0.8;
}