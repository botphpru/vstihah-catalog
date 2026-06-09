<?php

namespace App\Controllers;

use App\Core\Controller;

class ErrorController extends FrontendController
{
    public function errorPage(string $errClass, string $errorStr) {

        switch ($errClass) {
            case 'NotFoundException':
                $code = 404;
                $title = $code. ' - Страница не найдена';
                $textError = 'Страница не найдена. Попробуйте перейти на <a href="https://'.HOME_DOMAIN.'" class="text-decoration-underline">главную</a>.';
                break;
            default:
                $code = 503;
                $title = $code. ' - Ошибка сайта';
                $textError = 'Что-то пошло не так. Нам очень жаль что так произошло. В скором времени сайт заработает. Попробуйте перейти на <a href="https://'.HOME_DOMAIN.'" class="text-decoration-underline">главную</a>.';
        }
        $errorStrRes = $textError;
        http_response_code($code);
        $this->render('pages/error', [
            'meta_title' => $title,
            'code' => $code,
            'textError' => $textError,
        ]);
    }
}