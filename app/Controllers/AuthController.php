<?php

namespace App\Controllers;

use App\Core\Controller;

class AuthController extends FrontendController
{
    protected function setDefaultLayout(): void
    {
        $this->setLayout('auth');
    }
    public function login()
    {
        if(!isset($_GET['test'])) exit('А что это вы тут делаете?');
        // Если уже авторизован - редирект в админку
        if (app()->auth->isAdmin()) {
            header('Location: /admin123');
            exit;
        }
        $dop_head = '<meta name="robots" content="noindex, nofollow">';
        $this->render('login', ['dop_head' => $dop_head]);
    }

    public function loginForm()
    {
        $email = $this->request->get('email');
        $password = $this->request->get('password');

        if (app()->auth->login($email, $password)) {
            header('Location: /admin123');
            exit;
        }

        // Ошибка авторизации
        $this->render('login', [
            'error' => 'Неверный email или пароль'
        ]);
    }

    public function logout()
    {
        app()->auth->logout();
        header('Location: /auth/login');
        exit;
    }
}