<?php

namespace App\Services;

use App\Core\Exceptions\AIException;

class DeepSeekClient
{
    private string $apiKey;
    private string $apiUrl = 'https://api.deepseek.com/v1/chat/completions';
    private $full_response;

    // Пресеты настроек для разных задач
    private const PRESETS = [
        'default' => [
            'model' => 'deepseek-chat',
            'temperature' => 1.0,
            'top_p' => 0.9,
            'max_tokens' => 3024,
            'enable_thinking' => false,
        ],
        'poetry' => [
            'model' => 'deepseek-chat',
            'temperature' => 0.8,      // Чуть ниже для более связных рифм
            'top_p' => 0.9,            // Баланс креативности и фокуса
            'max_tokens' => 4096,      // Запас для размышлений + стих
            'enable_thinking' => true, // 🔥 Включаем Thinking Mode
        ],
        'creative' => [
            'model' => 'deepseek-chat',
            'temperature' => 1.2,
            'top_p' => 0.95,
            'max_tokens' => 3024,
            'enable_thinking' => false,
        ],
    ];

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Генерация ответа от API
     */
    public function generate(string $prompt, array $options = []): array
    {
        // Применяем пресет, если указан
        $preset = $options['preset'] ?? 'default';
        $baseOptions = self::PRESETS[$preset] ?? self::PRESETS['default'];

        // Мерджим с пользовательскими опциями (они приоритетнее)
        $params = array_merge(
            $baseOptions,
            [
                'messages' => [['role' => 'user', 'content' => $prompt]],
                'stream' => false,
            ],
            $options
        );

        // Убираем служебные ключи, которые не нужны в API-запросе
        unset($params['preset']);

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params, JSON_UNESCAPED_UNICODE));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new AIException(
                "API request failed: HTTP $httpCode. Response: $response" .
                ($curlError ? " | cURL error: $curlError" : '')
            );
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new AIException("Failed to decode API response: " . json_last_error_msg());
        }

        $this->full_response = $decoded;
        return $decoded;
    }

    /**
     * Получение только текста ответа (без thinking-блока)
     */
    public function getResponseText(string $prompt, array $options = []): ?string
    {
        $response = $this->generate($prompt, $options);
        return $response['choices'][0]['message']['content'] ?? null;
    }

    /**
     * Получение полного ответа, включая thinking (если включён)
     * @return array {content: string, thinking?: string}
     */
    public function getResponseWithThinking(string $prompt, array $options = []): ?array
    {
        $response = $this->generate($prompt, $options);
        $message = $response['choices'][0]['message'] ?? null;

        if (!$message) {
            return null;
        }

        // Thinking Mode может вернуть поле 'thinking' или 'reasoning_content'
        // в зависимости от версии API
        $result = ['content' => $message['content'] ?? ''];

        if (!empty($message['thinking'])) {
            $result['thinking'] = $message['thinking'];
        }
        if (!empty($message['reasoning_content'])) {
            $result['thinking'] = $message['reasoning_content'];
        }

        return $result;
    }

    public function getFullResponse()
    {
        return $this->full_response;
    }

    /**
     * Быстрый способ включить Thinking Mode для следующего запроса
     */
    public function withThinking(bool $enabled = true): self
    {
        // Возвращаем клон с изменёнными настройками — можно расширить при необходимости
        // Пока просто документационный метод, реальное включение — через 'preset' или $options
        return $this;
    }
}