<?php
// cron/clean_poems.php — очистка poems от "битых" записей
require_once __DIR__ . '/../config/init.php';
set_error_handler('errors_handler');
require_once ROOT . '/vendor/autoload.php';

$text = 'Когда в февраль влетает поток,  \nВрач София, моя дремлющая песня, —  \nМикстура счастья падает в росток,  \nГде валентинка станет лишь предвестней.';
test(isValidRussianPoem($text));
/**
 * Валидация текста стиха: только русский язык
 * @param string $text
 * @return bool
 */
function isValidRussianPoem(string $text): bool
{
    // 1. Убираем markdown-блоки кода
    $text = preg_replace('/^```[\w]*\s*|\s*```$/m', '', trim($text));

    // Минимальная длина (защита от обрезанных ответов)
    if (mb_strlen($text, 'UTF-8') < 30) {
        return false;
    }

    // 2. Запрещённые символы: ТОЛЬКО CJK-иероглифы и эмодзи
    // \x{4E00}-\x{9FFF}  : Китайские иероглифы (включая 雪, 的 и т.д.)
    // \x{3040}-\x{30FF}  : Японская азбука
    // \x{AC00}-\x{D7AF}  : Корейский
    // \x{1F300}-\x{1F9FF}: Эмодзи
    $forbidden = '/[\x{4E00}-\x{9FFF}\x{3400}-\x{4DBF}\x{3040}-\x{309F}\x{30A0}-\x{30FF}\x{AC00}-\x{D7AF}\x{1F300}-\x{1F9FF}]/u';

    if (preg_match($forbidden, $text)) {
        return false;
    }

    // 3. Считаем соотношение кириллицы ТОЛЬКО среди букв
    // \p{L} — любые буквы (кириллица, латиница и т.д.)
    $lettersOnly = preg_replace('/[^\p{L}]/u', '', $text);

    if (mb_strlen($lettersOnly, 'UTF-8') < 15) {
        return false; // Слишком мало букв
    }

    $cyrillicCount = preg_match_all('/\p{Cyrillic}/u', $lettersOnly);
    $ratio = $cyrillicCount / mb_strlen($lettersOnly, 'UTF-8');

    // Минимум 80% букв должны быть кириллицей
    return $ratio >= 0.8;
}