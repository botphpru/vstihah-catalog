<?php

require_once __DIR__ . '/../config/init.php'; //Подключение настроек сайта
set_error_handler('errors_handler'); //Регистрация пользовательской функции
require_once ROOT . '/vendor/autoload.php'; //Автозагрузка классов


try {
    $app = new \App\Core\Application();
    require_once HELPERS . '/helpers.php';
    //$app->run();
    //объявили массив всех ссылок
    $arrLinks = [];

    ////главная
    $arrLinks[] = [
        'loc' => 'https://'.HOME_DOMAIN,
    ];
    $arrLinks[] = [
        'loc' => 'https://'.HOME_DOMAIN.'/blog',
    ];
    $arrLinks[] = [
        'loc' => 'https://'.HOME_DOMAIN.'/about',
    ];
    $arrLinks[] = [
        'loc' => 'https://'.HOME_DOMAIN.'/contacts',
    ];

    //базовая приставка
    $url = 'https://'.HOME_DOMAIN.'/';
    //собираем верхний уровень
    $events = \App\Models\Event::findAll();
    foreach ($events as $event) {
        $arrLinks[] = [
            'loc' => $url.$event->slug,
        ];
    }

    $genres = \App\Models\Genre::findAll();
    foreach ($genres as $genre) {
        $arrLinks[] = [
            'loc' => $url.$genre->slug,
        ];
    }
    $names = \App\Models\Name::findAll();
    foreach ($names as $name) {
        $arrLinks[] = [
            'loc' => $url.$name->slug,
        ];
    }
    $recipients = \App\Models\Recipient::findAll();
    foreach ($recipients as $recipient) {
        $arrLinks[] = [
            'loc' => $url.$recipient->slug,
        ];
    }

    $code = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';

    foreach ($arrLinks as $arrLink) {
        $code .= '
<url>
    <loc>'.$arrLink['loc'].'</loc>';
        if(isset($arrLink['lastmod'])){
            $code .= '<lastmod>'.$arrLink['lastmod'].'</lastmod>';
        }
        $code .= '</url>';
    }

    $code .= '
</urlset>';

    file_put_contents(WWW . '/sitemap.xml', $code);

} catch (Throwable $e) {
    \App\Core\ErrorHandler::handleException($e);
}
