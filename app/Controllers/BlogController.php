<?php

namespace App\Controllers;

use App\Controllers\FrontendController;
use App\Core\Exceptions\NotFoundException;

class BlogController extends FrontendController
{

    public function getBlog()
    {
        $canonical = 'https://'.HOME_DOMAIN.'/blog';
        //генерим хлебные крошки
        $breadcrumbsArr = [
            'Главная' => 'https://'.HOME_DOMAIN,
            'Блог' => 'https://'.HOME_DOMAIN.'/blog',
        ];

        $meta_title = 'Блог ВСтихах.ру: про стихи, поздравления и живые моменты';

        //получаем номер страницы
        $page = request()->getNumberPage();
        //получаем настройки сколько нужно выводить ссылок в локации
        $limit = 9;

        $total_count = \App\Models\BlogPost::getCountTotal(true);
        // Вычисляем максимальный номер страницы
        $max_page = $total_count > 0 ? ceil($total_count / $limit) : 1;
        // Если запрошенная страница больше максимальной, сбрасываем на первую
        if ($page > $max_page) $page = 1;
        $offset = ($page - 1) * $limit;

        if($page > 1) $meta_title .= ' - страница '.$page;

        $posts = \App\Models\BlogPost::getLimitOffsetByOrderId($limit, $offset, 'DESC', true);

        $pagConfig = array(
            'baseURL' => $canonical,
            'totalRows' => $total_count,
            'perPage' => $limit
        );
        $pagination =  new \App\Services\Pagination($pagConfig);         //инициализируем класс pagination
        $pagination_html = $pagination->createLinks();

        $blog_posts_block = $this->renderBlock('blocks/blog_posts', ['posts' => $posts]);

        $this->render('pages/blog', [
            'meta_title' => $meta_title,
            'meta_desc' => 'Заметки о том, как словами передавать чувства: про стихи, поздравления и то, что остаётся между строк.',
            'page_title' => 'Не про рифмы, а про людей: заметки о поздравлениях',
            'page_desc' => 'Этот блог — про то, как говорить от души, когда слова важнее идеальных рифм. Про живые эмоции, которые и делают поздравление настоящим.',
            'canonical' => $canonical,
            'breadcrumbs_html' => $this->renderBlock('blocks/breadcrumbs', ['breadcrumbsArr' => $breadcrumbsArr]),
            'breadcrumbs_json' => $this->renderBlock('blocks/breadcrumbs_json', ['breadcrumbsArr' => $breadcrumbsArr]),
            'pagination_html' => $pagination_html,
            'blog_posts_block' => $blog_posts_block,
        ]);
    }

    public function getBlogPost($post_alias) {


        $post = \App\Models\BlogPost::findByAlias($post_alias, true);
        if(!$post) {
            throw new NotFoundException('Post not found');
        }

        $canonical = 'https://'.HOME_DOMAIN.'/blog/'.$post->alias;
        //генерим хлебные крошки
        $breadcrumbsArr = [
            'Главная' => 'https://'.HOME_DOMAIN,
            'Блог' => 'https://'.HOME_DOMAIN.'/blog',
            $post->name => $canonical,
        ];

        if($post->text_format == 'markdown') {
            $Parsedown = new \App\Services\Parsedown();
            $text = $Parsedown->text($post->text);
        } else {
            $text = $post->text;
        }


        // Предыдущая/следующая статья
        $prev_post = \App\Models\BlogPost::getPrevPost($post->id, true);
        $next_post = \App\Models\BlogPost::getNextPost($post->id, true);

        //Сохраняем текущее кол-во просмотров
        $view_count = $post->view_count;
        //Обновляем счетчик просмотров
        $post->updateViewCount();

        // Формируем абсолютный URL для обложки статьи (берем логику из вашего шаблона)
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $og_image = $protocol . HOME_DOMAIN . '/images/blogpost/' . $post->id . '/1200_' . md5($post->id) . '.webp';

        // Подготавливаем даты и безопасные строки
        $pub_time = date('c', strtotime($post->add_at));
        $mod_time = $post->public_upd_at ? date('c', strtotime($post->public_upd_at)) : $pub_time;

        $safe_title = htmlspecialchars($post->meta_title ?: $post->page_title, ENT_QUOTES);
        $safe_desc = htmlspecialchars($post->meta_desc ?: $post->page_desc, ENT_QUOTES);


        // Генерируем массив для application/ld+json
        $schema_data = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $canonical
            ],
            'headline' => $post->page_title,
            'description' => $post->page_desc,
            'image' => $og_image,
            'datePublished' => $pub_time,
            'dateModified' => $mod_time,
            'author' => [
                '@type' => 'Person',
                'name' => 'Семён Авдосов', // Имя автора
                'url' => $protocol . HOME_DOMAIN . '/about'
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'ВСтихах.Ру',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $protocol . HOME_DOMAIN . '/images/logo.png'
                ]
            ]
        ];


        $schema_json = json_encode($schema_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // Формируем HTML строку для dop_head
        $dop_head = <<<HTML
<meta property="og:title" content="{$safe_title}">
<meta property="og:description" content="{$safe_desc}">
<meta property="og:type" content="article">
<meta property="og:url" content="{$canonical}">
<meta property="og:image" content="{$og_image}">
<meta property="og:site_name" content="ВСтихах.Ру">
<meta property="article:published_time" content="{$pub_time}">
<meta property="article:modified_time" content="{$mod_time}">
<meta property="article:section" content="Блог о поздравлениях в стихах">
HTML;
        //Добавляем twitter
        $dop_head .= <<<HTML

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{$safe_title}">
<meta name="twitter:description" content="{$safe_desc}">
<meta name="twitter:image" content="{$og_image}">

<script type="application/ld+json">
{$schema_json}
</script>
HTML;

        $this->render('pages/blog_post', [
            'meta_title' => $post->meta_title,
            'meta_desc' => $post->meta_desc,
            'page_title' => $post->page_title,
            'page_desc' => $post->page_desc,
            'text' => $text,
            'canonical' => $canonical,
            'breadcrumbs_html' => $this->renderBlock('blocks/breadcrumbs', ['breadcrumbsArr' => $breadcrumbsArr]),
            'breadcrumbs_json' => $this->renderBlock('blocks/breadcrumbs_json', ['breadcrumbsArr' => $breadcrumbsArr]),
            'post' => $post,
            'prev_post' => $prev_post,
            'next_post' => $next_post,
            'view_count' => $view_count,
            'dop_head' => $dop_head,
        ]);
    }
}