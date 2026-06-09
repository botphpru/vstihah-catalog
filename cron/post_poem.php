<?php
//cron/post_poem.php
require_once __DIR__ . '/../config/init.php'; //Подключение настроек сайта
set_error_handler('errors_handler'); //Регистрация пользовательской функции
require_once ROOT . '/vendor/autoload.php'; //Автозагрузка классов



try {
    $app = new \App\Core\Application();
    require_once HELPERS . '/helpers.php';
    //$app->run();

    //Получаем стихотворение
    $poem = \App\Models\Poem::getForPostingToTg();
    if(!$poem){
        exit('Нету стиха для публикации для публикации в TG');
    }
    echo 'Poem: '.$poem->id.'<br>';

    //подготавливает пост
    $text = "***\n\n".$poem->text."\n---\n";

    //Добавляем ссылки на разделы
    $event = \App\Models\Event::getByPoemId($poem->id);
    if($event){
        $text .= "\n".'Событие: <a href="https://'.HOME_DOMAIN.'/'.$event->slug.'">'.$event->name.'</a>';
    }
    $recipient = \App\Models\Recipient::getByPoemId($poem->id);
    if($recipient){
        $text .= "\n".'Для кого: <a href="https://'.HOME_DOMAIN.'/'.$recipient->slug.'">'.$recipient->name.'</a>';
    }
    $name = \App\Models\Name::getByPoemId($poem->id);
    if($name){
        $text .= "\n".'Имя: <a href="https://'.HOME_DOMAIN.'/'.$name->slug.'">'.$name->name.'</a>';
    }
    $genre = \App\Models\Genre::getByPoemId($poem->id);
    if($genre){
        $text .= "\n".'Стиль: <a href="https://'.HOME_DOMAIN.'/'.$genre->slug.'">'.$genre->name.'</a>';
    }

    //публикуем
    $tg_bot = \App\Services\TgBot::init();

    $result = $tg_bot->sendMessage(POEMS_CHANNEL, $text);

    $poem->updateByArr(['is_tg_published' => 1]);
    test($result);


} catch (Throwable $e) {
    \App\Core\ErrorHandler::handleException($e);
}
