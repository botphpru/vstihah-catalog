<?php

namespace App\Controllers;


use App\Models\Page;

class HomeController extends FrontendController
{
    public function index() {


        $events = \App\Models\Event::getAllWithCount(true);
        $recipients = \App\Models\Recipient::getAllWithCount(true);
        $names = \App\Models\Name::getAllWithCount(true);
        $genres = \App\Models\Genre::getAllWithCount(true);

        //Посты блога
        $posts = \App\Models\BlogPost::getLimitByOrderId(6,'DESC', false);
        $blog_posts_block = $this->renderBlock('blocks/blog_posts', ['posts' => $posts]);

        //дополнительный head
        $canonical = 'https://'.HOME_DOMAIN;
        $meta_title = 'ВСтихах.Ру — поздравления в стихах, которые хочется дарить';
        $meta_desc = 'Душевные стихи на любой повод: для близких, друзей, коллег. Находите слова, которые говорят то, что чувствуете. Без шаблонов — только от души.';
        $og_image = 'https://'.HOME_DOMAIN.'/images/home.jpg';

        // Формируем HTML строку для dop_head
        $dop_head = <<<HTML
<meta property="og:title" content="{$meta_title}">
<meta property="og:description" content="{$meta_desc}">
<meta property="og:type" content="website">
<meta property="og:url" content="{$canonical}">
<meta property="og:image" content="{$og_image}">
<meta property="og:site_name" content="ВСтихах.Ру">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{$meta_title}">
<meta name="twitter:description" content="{$meta_desc}">
<meta name="twitter:image" content="{$og_image}">
HTML;

        $this->render('pages/home', [
            'meta_title' => $meta_title,
            'meta_desc' => $meta_desc,
            'canonical' => $canonical,
            'blog_posts_block' => $blog_posts_block,
            'events' => $events,
            'recipients' => $recipients,
            'names' => $names,
            'genres' => $genres,
            'dop_head' => $dop_head
        ]);
    }
}