<?php

namespace App\Core\Middleware;

/**
 * Middleware для проверки авторизации и прав администратора.
 *
 * Предназначен для защиты маршрутов админ-панели.
 * Блокирует доступ пользователям без административных прав,
 * перенаправляя их на страницу входа.
 *
 * @package App\Core\Middleware
 */
class AdminAuth
{
    /**
     * Проверяет, является ли текущий пользователь администратором.
     *
     * Выполняет проверку через app()->auth->isAdmin().
     * Если проверка возвращает false:
     * - Устанавливает HTTP-заголовок Location: /auth/login
     * - Немедленно завершает выполнение скрипта через exit
     *
     * @return void
     */
    public function handle(): void
    {
        if (!app()->auth->isAdmin()) {
            header('Location: /auth/login');
            exit;
        }
    }
}