<?php
//Подключение настроек сайта
require_once __DIR__ . '/../config/init.php';
//Регистрация пользовательской функции отлова ошибок
set_error_handler('errors_handler');
//Автозагрузка классов
require_once ROOT . '/vendor/autoload.php';

try {
    //Создание приложения
    $app = new \App\Core\Application();
    //Подключение функций помощников
    require_once HELPERS . '/helpers.php';
    //Запуск приложения
    $app->run();
} catch (Throwable $e) {
    //Отлов ошибок для логирования
    \App\Core\ErrorHandler::handleException($e);
}
