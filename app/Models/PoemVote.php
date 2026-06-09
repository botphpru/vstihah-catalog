<?php

namespace App\Models;

use App\Core\Model;

class PoemVote extends Model
{
    protected static function getTableName(): string
    {
        return 'poem_votes';
    }

    /**
     * Получить голос пользователя по poem_id и session_id
     */
    public static function getByPoemAndSession(int $poemId, string $sessionId): ?self
    {
        return self::getOneByArr([
            'poem_id' => $poemId,
            'session_id' => $sessionId
        ]);
    }

    /**
     * Создать или обновить голос (упрощённый метод)
     */
    public static function upsertVote(int $poemId, string $sessionId, int $vote): void
    {
        $existing = self::getByPoemAndSession($poemId, $sessionId);

        if ($existing) {
            if ($existing->vote === $vote) {
                // Тот же голос → удаляем
                self::deleteByArr(['id' => $existing->id]);
            } else {
                // Другой голос → обновляем
                $existing->updateByArr(['vote' => $vote]);
            }
        } else {
            // Новый голос → создаём
            self::insertByArr([
                'poem_id' => $poemId,
                'session_id' => $sessionId,
                'vote' => $vote
            ]);
        }
    }

    /**
     * Удалить голос (для отмены)
     */
    public static function removeVote(int $poemId, string $sessionId): void
    {
        self::deleteByArr([
            'poem_id' => $poemId,
            'session_id' => $sessionId
        ]);
    }
}