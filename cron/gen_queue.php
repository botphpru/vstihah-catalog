<?php
// cron/gen_queue.php
require_once __DIR__ . '/../config/init.php';
set_error_handler('errors_handler');
require_once ROOT . '/vendor/autoload.php';

try {
    $app = new \App\Core\Application();
    require_once HELPERS . '/helpers.php';

    // === Настройки ===
    $quatrainsOptions = [1, 4];
    $outputLimit = 10000;      // Сколько записей показать в браузере
    $doInsert = true;         // ⚠️ ПОСТАВЬТЕ true, когда проверите вывод
    $insertChunkSize = 500;    // Размер пачки для INSERT

    $outputCount = 0;
    $insertBuffer = [];
    $totalGenerated = 0;

    // === Загружаем все справочники в память (индексируем по id) ===
    $events = \App\Models\Event::findAll();
    $genres = \App\Models\Genre::findAll();
    $names = \App\Models\Name::findAll();
    $recipients = \App\Models\Recipient::findAll();

    $eventsById = \App\Models\Event::getRepo($events);
    $genresById = \App\Models\Genre::getRepo($genres);
    $namesById = \App\Models\Name::getRepo($names);
    $recipientsById = \App\Models\Recipient::getRepo($recipients);

    // === Вспомогательная функция проверки совместимости полов ===
    $isCompatible = function(string $g1, string $g2): bool {
        return $g1 === 'unisex' || $g2 === 'unisex' || $g1 === $g2;
    };

    // === Вспомогательная функция: добавить комбинацию ===
    $addCombo = function(
        int $quatrains,  // 🔥 Теперь целое число, а не строка
        ?int $eventId = null,
        ?int $genreId = null,
        ?int $nameId = null,
        ?int $recipientId = null
    ) use (&$outputCount, $outputLimit, $doInsert, &$insertBuffer, &$totalGenerated,
        $eventsById, $genresById, $namesById, $recipientsById, $insertChunkSize) {

        global $argv;
        $isCli = php_sapi_name() === 'cli';
        $totalGenerated++;

        // === Формируем строку для визуальной проверки ===
        $parts = [];
        $parts[] = "Q:{$quatrains}";  // 🔥 Показываем количество четверостиший

        if ($eventId && isset($eventsById[$eventId])) {
            $parts[] = 'E:' . $eventsById[$eventId]->name;
        }
        if ($genreId && isset($genresById[$genreId])) {
            $parts[] = 'G:' . $genresById[$genreId]->name;
        }
        if ($nameId && isset($namesById[$nameId])) {
            $parts[] = 'N:' . $namesById[$nameId]->name;
        }
        if ($recipientId && isset($recipientsById[$recipientId])) {
            $parts[] = 'R:' . $recipientsById[$recipientId]->name;
        }

        if (count($parts) === 1) {
            $parts[] = '(одиночный)';
        }

        $outputLine = implode(' | ', $parts);

        // Вывод (без изменений)
        if ($outputCount < $outputLimit) {
            if ($isCli) {
                echo $outputLine . PHP_EOL;
            } else {
                echo htmlspecialchars($outputLine) . "<br>\n";
                flush();
            }
            $outputCount++;
        }

        // Буферизация для INSERT 🔥 Используем quatrains вместо size
        if ($doInsert) {
            $insertBuffer[] = [
                'quatrains' => $quatrains,  // 🔥 Ключ changed
                'event_id' => $eventId,
                'genre_id' => $genreId,
                'name_id' => $nameId,
                'recipient_id' => $recipientId,
                'generated_count' => 0,
            ];

            if (count($insertBuffer) >= $insertChunkSize) {
                \App\Models\GenerationQueue::insertIgnoreBatch($insertBuffer, $insertChunkSize);
                $insertBuffer = [];
            }
        }
    };

    // === ГЕНЕРАЦИЯ КОМБИНАЦИЙ ===

    foreach ($quatrainsOptions as $quatrains) {

        // ── 1. Одиночные привязки (только один параметр + size) ──

        // Только событие
        foreach ($events as $event) {
            $addCombo($quatrains, $event->id);
        }
        // Только жанр
        foreach ($genres as $genre) {
            $addCombo($quatrains, null, $genre->id);
        }
        // Только имя
        foreach ($names as $name) {
            $addCombo($quatrains, null, null, $name->id);
        }
        // Только получатель
        foreach ($recipients as $recipient) {
            $addCombo($quatrains, null, null, null, $recipient->id);
        }

        // ── 2. Двойные привязки ──

        // event + genre
        foreach ($events as $event) {
            foreach ($genres as $genre) {
                $addCombo($quatrains, $event->id, $genre->id);
            }
        }

        // event + name (с проверкой gender)
        foreach ($events as $event) {
            foreach ($names as $name) {
                if ($isCompatible($event->gender, $name->gender)) {
                    $addCombo($quatrains, $event->id, null, $name->id);
                }
            }
        }

        // event + recipient (с проверкой gender)
        foreach ($events as $event) {
            foreach ($recipients as $recipient) {
                if ($isCompatible($event->gender, $recipient->gender)) {
                    $addCombo($quatrains, $event->id, null, null, $recipient->id);
                }
            }
        }

        // genre + name
        foreach ($genres as $genre) {
            foreach ($names as $name) {
                $addCombo($quatrains, null, $genre->id, $name->id);
            }
        }

        // genre + recipient
        foreach ($genres as $genre) {
            foreach ($recipients as $recipient) {
                $addCombo($quatrains, null, $genre->id, null, $recipient->id);
            }
        }

        // name + recipient (с проверкой gender)
        foreach ($names as $name) {
            foreach ($recipients as $recipient) {
                if ($isCompatible($name->gender, $recipient->gender)) {
                    $addCombo($quatrains, null, null, $name->id, $recipient->id);
                }
            }
        }

        // ── 3. Тройные привязки ──

        // event + genre + name
        foreach ($events as $event) {
            foreach ($genres as $genre) {
                foreach ($names as $name) {
                    if ($isCompatible($event->gender, $name->gender)) {
                        $addCombo($quatrains, $event->id, $genre->id, $name->id);
                    }
                }
            }
        }

        // event + genre + recipient
        foreach ($events as $event) {
            foreach ($genres as $genre) {
                foreach ($recipients as $recipient) {
                    if ($isCompatible($event->gender, $recipient->gender)) {
                        $addCombo($quatrains, $event->id, $genre->id, null, $recipient->id);
                    }
                }
            }
        }

        // event + name + recipient
        foreach ($events as $event) {
            foreach ($names as $name) {
                if (!$isCompatible($event->gender, $name->gender)) continue;
                foreach ($recipients as $recipient) {
                    if ($isCompatible($event->gender, $recipient->gender)
                        && $isCompatible($name->gender, $recipient->gender)) {
                        $addCombo($quatrains, $event->id, null, $name->id, $recipient->id);
                    }
                }
            }
        }

        // genre + name + recipient
        foreach ($genres as $genre) {
            foreach ($names as $name) {
                foreach ($recipients as $recipient) {
                    if ($isCompatible($name->gender, $recipient->gender)) {
                        $addCombo($quatrains, null, $genre->id, $name->id, $recipient->id);
                    }
                }
            }
        }

        // ── 4. Полные комбинации (4 привязки) ──

        foreach ($events as $event) {
            foreach ($genres as $genre) {
                foreach ($names as $name) {
                    if (!$isCompatible($event->gender, $name->gender)) continue;
                    foreach ($recipients as $recipient) {
                        if ($isCompatible($event->gender, $recipient->gender)
                            && $isCompatible($name->gender, $recipient->gender)) {
                            $addCombo($quatrains, $event->id, $genre->id, $name->id, $recipient->id);
                        }
                    }
                }
            }
        }
    }

    // === Финальная запись остатков буфера в БД ===
    if ($doInsert && !empty($insertBuffer)) {
        \App\Models\GenerationQueue::insertIgnoreBatch($insertBuffer, $insertChunkSize);
    }

    // === Итоговый отчет ===
    $isCli = php_sapi_name() === 'cli';
    if ($isCli) {
        echo PHP_EOL . "===== ОТЧЕТ =====" . PHP_EOL;
        echo "Всего сгенерировано комбинаций: $totalGenerated" . PHP_EOL;
        echo "Показано в выводе: $outputCount" . PHP_EOL;
        echo "Запись в БД: " . ($doInsert ? "ВКЛЮЧЕНА" : "ВЫКЛЮЧЕНА (закомментировано)") . PHP_EOL;
    } else {
        echo "<hr><strong>Отчет:</strong><br>";
        echo "Всего комбинаций: $totalGenerated <br>";
        echo "Показано: $outputCount <br>";
        echo "Запись в БД: " . ($doInsert ? "✅ ВКЛЮЧЕНА" : "❌ ВЫКЛЮЧЕНА") . "<br>";
    }

} catch (Throwable $e) {
    \App\Core\ErrorHandler::handleException($e);
}