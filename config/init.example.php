<?php

define("ROOT", dirname(__DIR__));
const DEBUG = false;
const WWW = ROOT . '/public';
const UPLOADS = ROOT . '/uploads';
const CACHE_DIR = ROOT . '/tmp/cache';
const APP = ROOT . '/app';
const HELPERS = ROOT . '/helpers';
const CONFIG = ROOT . '/config';
const VIEWS = APP . '/Views';
const LOGS = ROOT . '/logs';
const HOME_DOMAIN = '';
const LOGIN_PAGE = '';
const PREFIX_CACHE = '123';

const DEEPSEEK_API = '';

const SITE_NAME = 'Каталог стихов';

const CACHE_SECONDS = 86400;

//TG настройки
const BOT_TOKEN = '';
const NOTICE_CHANNEL = '';

//канал для статей
const POEMS_CHANNEL = '';
//канал для новых ссылок
const BLOG_CHANNEL = '';

const DB = [
    'host' => 'localhost',
    'dbname' => 'mydb',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_CLASS,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
    ],
];


function test($data, $exit = false)
{
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    if ($exit) {
        exit;
    } else {
        echo '<hr>';
    }
}

function errors_handler($errno, $errstr, $errfile, $errline){ //Пользовательская функция обработчика ошибок PHP
    $errors = array( //Формирования массива констант ошибок
        E_WARNING => 'E_WARNING',
        E_NOTICE => 'E_NOTICE',
        E_CORE_WARNING => 'E_CORE_WARNING',
        E_COMPILE_WARNING => 'E_COMPILE_WARNING',
        E_USER_ERROR => 'E_USER_ERROR',
        E_USER_WARNING => 'E_USER_WARNING',
        E_USER_NOTICE => 'E_USER_NOTICE',
        E_STRICT => 'E_STRICT',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        E_DEPRECATED => 'E_DEPRECATED',
        E_USER_DEPRECATED => 'E_USER_DEPRECATED'
    );
    $date = date ("Y-m-d G:i:s"); //Получение даты и времени возникновения ошибки
    $url = $_SERVER['REQUEST_URI']; //Получение URL страницы, при формировании которой произошла ошибка
    $error_message = "$date $errors[$errno] $errstr в файле $errfile в $errline строке (URL: $url)\n"; //Сформированное сообщение об ошибке
    if(DEBUG) echo '<br/>'.htmlspecialchars($error_message).'<br/>';
    $filename = LOGS . '/php_errors/'.date("Y-m-d").'.txt'; //Имя файла с директорией
    if(!is_dir(pathinfo($filename, PATHINFO_DIRNAME))) {
        mkdir(pathinfo($filename, PATHINFO_DIRNAME), 0777, true);
    }
    file_put_contents($filename, $error_message, FILE_APPEND | LOCK_EX); //Добавляем сообщение об ошибке в лог-файл (в конец файла). Если файла не существует, будет создан

    return true;
}