<?php

namespace App\Core;

use App\Models\User;
use App\Core\Exceptions\BaseException;

/**
 * Компонент управления аутентификацией пользователей.
 *
 * Отвечает за:
 * - Вход по логину/паролю с проверкой подтверждения аккаунта
 * - Поддержание сессии и "запомнить меня" через cookie-токен
 * - Проверку прав доступа (в том числе администратора)
 * - Выход из системы с очисткой сессии и токена
 *
 * @package App\Core
 */
class Auth
{
    /** @var string Ключ сессии для хранения ID пользователя */
    private const SESSION_KEY = 'user_id';

    /** @var string Имя cookie для хранения токена авторизации */
    private const COOKIE_TOKEN = 'auth_token';

    /** @var int Время жизни токена в секундах (2 дня) */
    private const COOKIE_LIFETIME = 2 * 24 * 60 * 60;

    /**
     * Выполняет вход пользователя по email и паролю.
     *
     * Алгоритм:
     * 1. Ищет пользователя по email через User::getOneByArr()
     * 2. Проверяет: существование пользователя, подтверждение аккаунта (isConfirmed()), корректность пароля (verifyPassword())
     * 3. При успехе: сохраняет ID в сессию и устанавливает токен в cookie
     *
     * @param string $email Email пользователя
     * @param string $password Пароль в открытом виде
     *
     * @return bool true при успешной авторизации, false при любой ошибке проверки
     *
     * @throws BaseException Если возникает ошибка на уровне модели User
     */
    public function login(string $email, string $password): bool
    {

        $user = User::getOneByArr(['email' => $email]);

        if (!$user || !$user->isConfirmed() || !$user->verifyPassword($password)) {
            return false;
        }

        $this->setSession($user->id);
        $this->setToken($user);

        return true;
    }
    /**
     * Выполняет выход пользователя из системы.
     *
     * Действия:
     * 1. Если в сессии есть user_id — загружает пользователя и вызывает clearToken()
     * 2. Удаляет ключ из $_SESSION
     * 3. Удаляет cookie с токеном (устанавливает пустое значение с истёкшим временем)
     *
     * @return void
     */
    public function logout(): void
    {
        if (isset($_SESSION[self::SESSION_KEY])) {
            $user = User::find($_SESSION[self::SESSION_KEY]);
            if ($user) {
                $user->clearToken();
            }
            unset($_SESSION[self::SESSION_KEY]);
        }

        setcookie(self::COOKIE_TOKEN, '', time() - 3600, '/');
    }
    /**
     * Проверяет, авторизован ли текущий пользователь.
     *
     * Порядок проверки:
     * 1. Наличие user_id в сессии → сразу true
     * 2. Если сессии нет — попытка входа по токену из cookie через loginByToken()
     *
     * @return bool true если пользователь аутентифицирован, false иначе
     */
    public function check(): bool
    {
        // Проверка сессии
        if (isset($_SESSION[self::SESSION_KEY])) {
            return true;
        }

        // Проверка токена из куки
        return $this->loginByToken();
    }
    /**
     * Возвращает объект текущего пользователя или null.
     *
     * Сначала выполняет проверку авторизации через check().
     * Если пользователь авторизован — загружает данные из БД по ID из сессии.
     *
     * @return User|null Объект пользователя или null если не авторизован / не найден
     */
    public function user(): ?User
    {
        if (!$this->check()) {
            return null;
        }

        $userId = $_SESSION[self::SESSION_KEY] ?? null;
        return $userId ? User::find($userId) : null;
    }
    /**
     * Проверяет, является ли текущий пользователь администратором.
     *
     * Получает текущего пользователя через user() и вызывает у него isAdmin().
     *
     * @return bool true если пользователь авторизован и имеет права администратора
     */
    public function isAdmin(): bool
    {
        $user = $this->user();
        return $user && $user->isAdmin();
    }
    /**
     * Выполняет вход по токену из cookie.
     *
     * Алгоритм:
     * 1. Читает токен из $_COOKIE[COOKIE_TOKEN]
     * 2. Ищет пользователя по токену через User::findByToken()
     * 3. Проверяет подтверждение аккаунта (isConfirmed())
     * 4. При успехе — сохраняет ID в сессию (токен в cookie не обновляется)
     *
     * @return bool true при успешном входе по токену, false при любой ошибке
     */
    private function loginByToken(): bool
    {
        $token = $_COOKIE[self::COOKIE_TOKEN] ?? '';

        if (!$token) {
            return false;
        }

        $user = User::findByToken($token);

        if (!$user || !$user->isConfirmed()) {
            return false;
        }

        $this->setSession($user->id);
        return true;
    }
    /**
     * Сохраняет ID пользователя в сессию.
     *
     * @param int $userId ID авторизованного пользователя
     *
     * @return void
     */
    private function setSession(int $userId): void
    {
        $_SESSION[self::SESSION_KEY] = $userId;
    }
    /**
     * Генерирует и устанавливает токен авторизации в cookie.
     *
     * Действия:
     * 1. Вызывает $user->generateToken() для получения нового токена
     * 2. Устанавливает cookie с параметрами:
     *    - имя: COOKIE_TOKEN
     *    - время жизни: текущее время + COOKIE_LIFETIME
     *    - путь: '/' (доступ на всём домене)
     *    - HttpOnly: true (недоступен для JavaScript)
     *    - Secure: false (передаётся и по HTTP)
     *
     * @param User $user Объект пользователя для генерации токена
     *
     * @return void
     */
    private function setToken(User $user): void
    {
        $token = $user->generateToken();
        setcookie(self::COOKIE_TOKEN, $token, time() + self::COOKIE_LIFETIME, '/', '', false, true);
    }
}