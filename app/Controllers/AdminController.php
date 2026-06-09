<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Middleware\AdminAuth;
use App\Core\Request;
use App\Core\View;

/**
 * Абстрактный базовый контроллер для всех контроллеров админ-панели.
 *
 * Предназначен для централизованной настройки общего поведения админ-секции:
 * - Автоматическая проверка авторизации через middleware AdminAuth
 * - Установка стандартного layout 'admin' для всех представлений
 * - Наследование базовой логики из Controller
 *
 * Все контроллеры админки должны наследоваться от этого класса,
 * чтобы гарантировать единообразие аутентификации и оформления.
 *
 * @package App\Controllers
 * @abstract
 * @extends Controller
 * @see AdminAuth::handle()
 */
abstract class AdminController extends Controller
{
    /**
     * Конструктор контроллера админ-панели.
     *
     * Расширяет базовый конструктор родительского класса, добавляя:
     * 1. Мгновенную проверку авторизации через вызов AdminAuth::handle()
     *    - При неуспешной проверке выполняется редирект на страницу входа
     *    - При успехе — выполнение продолжается
     * 2. Автоматическую установку layout 'admin' через setDefaultLayout()
     *    (вызывается в конструкторе родительского Controller)
     *
     * ⚠️ Важно: Этот конструктор выполняется при создании любого контроллера,
     * наследующего AdminController, поэтому проверка авторизации происходит
     * до выполнения любого действия (action).
     *
     * @param View $view Экземпляр системы рендеринга представлений
     * @param Request $request Объект текущего HTTP-запроса
     *
     * @return void
     *
     * @throws \Exception Если middleware AdminAuth не смог обработать запрос
     * @see Controller::__construct()
     * @see AdminAuth::handle()
     */
    public function __construct(View $view, Request $request)
    {
        parent::__construct($view, $request);
        (new AdminAuth())->handle();
    }

    /**
     * Устанавливает стандартный layout для всех представлений админ-панели.
     *
     * Фиксирует шаблон-обёртку 'admin' для единообразия оформления.
     * Этот метод автоматически вызывается в конструкторе родительского класса,
     * поэтому явно вызывать его не требуется.
     *
     * @return void
     */
    protected function setDefaultLayout(): void
    {
        $this->setLayout('admin');
    }
}