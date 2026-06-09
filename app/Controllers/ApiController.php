<?php

namespace App\Controllers;

use App\Controllers\FrontendController;
use App\Models\Poem;
use App\Models\PoemVote;

class ApiController extends FrontendController
{
    /**
     * Обработка AJAX-запроса на обновление рейтинга стиха
     * POST /api/poems/update_rating
     */
    public function poemRating(): void
    {
        header('Content-Type: application/json');

        try {
            // 1. Проверка метода
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('Method not allowed', 405);
            }

            // 2. Читаем RAW JSON из тела запроса
            $rawInput = file_get_contents('php://input');
            $data = json_decode($rawInput, true);

            if (!is_array($data)) {
                throw new \Exception('Invalid JSON payload', 400);
            }

            // 3. Проверка CSRF-токена (из заголовка, как шлёт ваш JS)
            $this->checkCsrfToken($data);

            // 4. Валидация входных данных (теперь из $data, а не $_POST)
            $poemId = filter_var($data['poem_id'] ?? null, FILTER_VALIDATE_INT);
            $vote = filter_var($data['value'] ?? null, FILTER_VALIDATE_INT);

            if (!$poemId || !in_array($vote, [1, -1], true)) {
                throw new \Exception('Invalid input data', 400);
            }

            // 5. Получаем стих
            $poem = Poem::getById($poemId);
            if (!$poem) {
                throw new \Exception('Poem not found', 404);
            }

            // 6. Получаем session_id
            $sessionId = session_id();
            if (!$sessionId) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $sessionId = session_id();
            }

            // 7. Логика голосования
            $existingVote = PoemVote::getByPoemAndSession($poemId, $sessionId);
            $currentRating = $poem->rating ?? 0;
            $newRating = $currentRating;

            if ($existingVote) {
                if ($existingVote->vote === $vote) {
                    // === Тот же голос: отменяем ===
                    PoemVote::removeVote($poemId, $sessionId);
                    $newRating = $currentRating - $vote;
                } else {
                    // === Другой голос: меняем ===
                    $existingVote->updateByArr(['vote' => $vote]);
                    $newRating = $currentRating + ($vote * 2);
                }
            } else {
                // === Новый голос: добавляем ===
                PoemVote::insertByArr([
                    'poem_id' => $poemId,
                    'session_id' => $sessionId,
                    'vote' => $vote
                ]);
                $newRating = $currentRating + $vote;
            }

            // 8. Обновляем рейтинг в таблице poems
            $poem->updateByArr(['rating' => $newRating]);

            // 9. Успешный ответ
            echo json_encode([
                'success' => true,
                'new_rating' => $newRating
            ], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }

        exit;
    }

    /**
     * Проверка CSRF-токена (адаптирована под JSON)
     */
    private function checkCsrfToken(array $data = []): void
    {
        // Токен может быть в заголовке (предпочтительно) или в теле запроса
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $data['csrf_token'] ?? '';
        $expected = $_SESSION['csrf_token'] ?? '';

        if (!$token || !$expected || !hash_equals($expected, $token)) {
            throw new \Exception('CSRF token validation failed', 403);
        }
    }

}