<?php
// cron/post_blog_post.php
require_once __DIR__ . '/../config/init.php'; //Подключение настроек сайта
set_error_handler('errors_handler'); //Регистрация пользовательской функции
require_once ROOT . '/vendor/autoload.php'; //Автозагрузка классов

$telegraph_token = '7c4c4ac469de1e2100c35630957ad59e4e0071ab1b0dff34c386a0a2a19b';


try {
    $app = new \App\Core\Application();
    require_once HELPERS . '/helpers.php';
    //$app->run();

    //Получаем пост
    $post = \App\Models\BlogPost::getForPostingToTg();
    if(!$post){
        exit('Нету поста для публикации в TG');
    }
    echo 'post: '.$post->name.'<br>';

    //подготавливает пост
    $img_path = 'https://'.HOME_DOMAIN.'/images/blogpost/'.$post->id.'/1200_'.md5($post->id).'.webp'; //Картинка поста
    $full_text = ''; //Текст для всей статьи
    $preview_text = ''; //Текст только для ссылки для чтения на сайте

    //Заголовок
    $full_text .= '<b>'.$post->page_title."</b>\n\n";
    $preview_text .= '<b>'.$post->page_title."</b>\n\n";

    //Анонс только для превью
    $preview_text .= $post->page_desc;

    //Инициируем бота для отправки
    $tg_bot = \App\Services\TgBot::init();

    //Полный текст
    $text = $post->text;
    if($post->text_format == 'markdown') {
        // Исправлен баг с переменной
        $parsedown = new \App\Services\Parsedown();
        $text = $parsedown->text($text);
    }
    $prepared_text = $tg_bot->prepareTextForTelegram($text);
    $full_text .= $prepared_text;

    //Ссылка на сайте
    $link = 'https://'.HOME_DOMAIN.'/blog/'.$post->alias;
    $full_text .= "\n\n".'<a href="'.$link.'">Читать на сайте</a>';

    //публикация на телеграph
    $telegraph = new \App\Services\TelegraphService();

    $dop_text = $telegraph->getRandomTextFooter();
    $htmlContent = $text.'<p><a href="'.$link.'">Читать на сайте</a></p>'.$dop_text;
    $htmlContent = str_replace('src="/images', 'src="https://'.HOME_DOMAIN.'/images', $htmlContent);

    $page = $telegraph->publishPage(
        accessToken: $telegraph_token,
        title: $post->page_title,
        htmlContent: $htmlContent,
        topImageUrl: $img_path,
        authorName: 'ВСтихах.Ру',        // Имя автора под заголовком
        authorUrl: 'https://vstihah.ru'  // Ссылка при клике на имя автора
    );

    if(isset($page['url'])) {
        $inline_keyboard = [
            'inline_keyboard' => [
                [ // Это первый (и единственный) ряд кнопок
                    [ // Это сама кнопка
                        'text' => 'Читать на сайте',
                        'url'  => $link
                    ],
                    [ // Это сама кнопка
                        'text' => 'Читать на telegraph',
                        'url'  => $page['url']
                    ]
                ]
            ]
        ];
        $full_text .= ' | <a href="'.$page['url'].'">Читать на telegraph</a>';
    } else {
        $inline_keyboard = [
            'inline_keyboard' => [
                [ // Это первый (и единственный) ряд кнопок
                    [ // Это сама кнопка
                        'text' => 'Читать на сайте',
                        'url'  => $link
                    ]
                ]
            ]
        ];
    }


    $preview_text .= "\n\n".'<a href="'.$link.'">Читать на сайте</a>';
    $result = $tg_bot->sendPhoto(BLOG_CHANNEL, $preview_text, $img_path, $inline_keyboard);

    //ставим пометку что статью опубликовали
    if(isset($page['url'])) {
        $post->updateByArr(['is_tg_published' => 1, 'telegraph_link' => $page['url']]);
    } else {
        $post->updateByArr(['is_tg_published' => 1]);
    }


    test($result);

} catch (Throwable $e) {
    \App\Core\ErrorHandler::handleException($e);
}
