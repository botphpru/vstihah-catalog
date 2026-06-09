<?php

function app(): \App\Core\Application
{
    return \App\Core\Application::$app;
}

function Db(): \App\Core\DB
{
    return \App\Core\Application::$app->db;
}

function request(): \App\Core\Request
{
    return \App\Core\Application::$app->request;
}

function pluralize(int $number, string $one, string $two, string $five): string
{
    $n = abs($number) % 100;
    $n1 = $n % 10;

    if ($n > 10 && $n < 20) {
        return $five;
    }
    if ($n1 > 1 && $n1 < 5) {
        return $two;
    }
    if ($n1 === 1) {
        return $one;
    }
    return $five;
}
function get_test($data, $exit = false)
{
    if(isset($_GET['test'])) {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
        if ($exit) {
            exit;
        } else {
            echo '<hr>';
        }
    }
}