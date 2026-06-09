<?php

namespace App\Controllers;

class PageController extends FrontendController
{
    public function privacy()
    {
        $canonical = 'https://'.HOME_DOMAIN.'/privacy';
        //генерим хлебные крошки
        $breadcrumbsArr = [
            'Главная' => 'https://'.HOME_DOMAIN,
            'Положения о конфиденциальности' => $canonical
        ];

        $this->render('pages/privacy', [
            'meta_title' => 'Положения о конфиденциальности и об использовании файлов cookie',
            'meta_desc' => 'Положения о конфиденциальности и об использовании файлов cookie',
            'page_title' => 'Положения о конфиденциальности и об использовании файлов cookie',
            'canonical' => $canonical,
            'breadcrumbs_html' => $this->renderBlock('blocks/breadcrumbs', ['breadcrumbsArr' => $breadcrumbsArr]),
            'breadcrumbs_json' => $this->renderBlock('blocks/breadcrumbs_json', ['breadcrumbsArr' => $breadcrumbsArr]),
        ]);
    }
    public function contacts()
    {
        $canonical = 'https://'.HOME_DOMAIN.'/contacts';
        //генерим хлебные крошки
        $breadcrumbsArr = [
            'Главная' => 'https://'.HOME_DOMAIN,
            'Контакты' => $canonical
        ];

        $this->render('pages/contacts', [
            'meta_title' => 'Контакты — Связаться с авторами проекта ВСтихах.ру',
            'meta_desc' => 'Есть идеи, крутые стихи или нашли ошибку? 💡 Все способы связи с автором vstihah.ru в одном месте: Telegram, почта и наш канал. Пишите, мы всегда открыты для общения! 📧',
            'page_title' => 'Контакты',
            'canonical' => $canonical,
            'breadcrumbs_html' => $this->renderBlock('blocks/breadcrumbs', ['breadcrumbsArr' => $breadcrumbsArr]),
            'breadcrumbs_json' => $this->renderBlock('blocks/breadcrumbs_json', ['breadcrumbsArr' => $breadcrumbsArr]),
        ]);
    }
    public function about() {
        $canonical = 'https://'.HOME_DOMAIN.'/about';
        //генерим хлебные крошки
        $breadcrumbsArr = [
            'Главная' => 'https://'.HOME_DOMAIN,
            'О сайте' => $canonical
        ];

        $this->render('pages/about', [
            'meta_title' => 'О проекте ВСтихах.ру — душевные поздравления в стихах на любой повод',
            'meta_desc' => 'Устали от банальных открыток? 💡 На ВСтихах.ру мы собрали тысячи поздравлений в стихах, удобно разложив их по именам, праздникам и настроению. Найдите идеальные слова за пару кликов! ⚡',
            'page_title' => 'О сайте',
            'canonical' => $canonical,
            'breadcrumbs_html' => $this->renderBlock('blocks/breadcrumbs', ['breadcrumbsArr' => $breadcrumbsArr]),
            'breadcrumbs_json' => $this->renderBlock('blocks/breadcrumbs_json', ['breadcrumbsArr' => $breadcrumbsArr]),
        ]);
    }
}