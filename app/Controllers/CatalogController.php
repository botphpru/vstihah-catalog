<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Exceptions\NotFoundException;

/**
 * Контроллер для отображения страниц каталога стихов.
 *
 * Отвечает за:
 * - Парсинг URL-сегментов и определение контекста страницы (событие, жанр, имя, получатель)
 * - Генерацию SEO-метаданных (title, description, canonical, Open Graph)
 * - Построение навигации (хлебные крошки, пагинация, фильтры)
 * - Выборку и отображение стихов с поддержкой сортировки и кэширования
 * - Обработку 301-редиректов на канонические URL
 *
 * @package App\Controllers
 * @extends FrontendController
 */
class CatalogController extends FrontendController
{
    /**
     * Допустимые значения параметра сортировки.
     * Формат: {поле}_{направление}, где поле: d=дата, r=рейтинг, l=длина
     *
     * @var array<string>
     */
    private array $sort_values = ['d_asc', 'd_desc', 'r_asc', 'r_desc', 'l_asc', 'l_desc'];
    /**
     * Человеко-читаемые названия для каждого варианта сортировки.
     *
     * @var array<string, string> Ключ: значение сортировки, Значение: текст для вывода
     */
    private array $sort_names = [
        'd_desc' => 'По дате: Сначала новые',
        'd_asc' => 'По дате: Сначала старые',
        'l_asc' => 'По длине: Сначала короткие',
        'l_desc' => 'По длине: Сначала длинные',
        'r_desc' => 'По рейтингу: Сначала популярные',
        'r_asc' => 'По рейтингу: Сначала не популярные',
    ];
    /**
     * Значение сортировки по умолчанию при первой загрузке страницы.
     *
     * @var string
     */
    private string $default_sort = 'd_desc';

    /**
     * Обрабатывает запрос к странице каталога стихов.
     *
     * Принимает до 4 сегментов пути из URL, каждый из которых может соответствовать
     * одному из типов сущностей: событие (event), получатель (recipient), имя (name), жанр (genre).
     *
     * Алгоритм работы:
     * 1. Фильтрует входные сегменты, удаляет null-значения
     * 2. Загружает все справочники (Event, Genre, Name, Recipient) и строит репозитории по slug
     * 3. Определяет контекст страницы через identifySegments()
     * 4. Генерирует мета-теги: title, H1, description, canonical URL
     * 5. Выполняет 301-редирект, если текущий URL не совпадает с каноническим
     * 6. Формирует хлебные крошки на основе контекста
     * 7. Обрабатывает параметры пагинации и сортировки из запроса
     * 8. Запрашивает стихи через Poem::getForCatalogPage() с кэшированием
     * 9. Отправляет заголовки кэширования (Last-Modified / 304) если есть данные
     * 10. Формирует массив для фильтра, пагинацию, блок последних постов блога
     * 11. Генерирует Open Graph и Twitter Card мета-теги
     * 12. Рендерит шаблон 'pages/catalog' со всеми данными
     *
     * ⚠️ Побочные эффекты:
     * - Может выполнить 301-редирект + exit при неканоническом URL
     * - Может отправить заголовок 304 Not Modified + exit при кэшировании
     * - Модифицирует заголовки ответа (Location, Last-Modified, OG-теги)
     *
     * @param string $slug_1 Первый обязательный сегмент пути
     * @param string|null $slug_2 Второй сегмент (опционально)
     * @param string|null $slug_3 Третий сегмент (опционально)
     * @param string|null $slug_4 Четвёртый сегмент (опционально)
     *
     * @return void Результат рендеринга шаблона
     *
     * @throws NotFoundException Если один из сегментов не найден ни в одном справочнике
     * @throws \App\Core\Exceptions\LogicException Если ошибка при рендеринге шаблона
     */
    public function catalogPage(string $slug_1, ?string $slug_2 = null, ?string $slug_3 = null, ?string $slug_4 = null)
    {
        // Роутер гарантирует минимум 1 сегмент, но фильтруем null на всякий случай
        $segments = array_values(array_filter([$slug_1, $slug_2, $slug_3, $slug_4]));

        // Загружаем справочники
        $events     = \App\Models\Event::findAll();
        $genres     = \App\Models\Genre::findAll();
        $names      = \App\Models\Name::findAll();
        $recipients = \App\Models\Recipient::findAll();

        // Репозитории вида: ['slug' => ModelObject]
        $repos = [
            'event'     => \App\Models\Event::getRepoSlug($events),
            'recipient' => \App\Models\Recipient::getRepoSlug($recipients),
            'name'      => \App\Models\Name::getRepoSlug($names),
            'genre'     => \App\Models\Genre::getRepoSlug($genres),
        ];

        // Идентифицируем сегменты → возвращает массив ['event' => $EventObj, 'name' => null, ...]
        $context = $this->identifySegments($segments, $repos);

        $meta_title = $this->buildMetaTitle($context); //генерим title
        $page_title = $this->buildPageTitle($context); //генерим h1
        $meta_desc = $this->buildMetaDesc($context); //генерим meta desc


        // Строим канонический URL
        $canonicalUrl = $this->buildCanonicalUrl($context);
        $canonical = 'https://'.HOME_DOMAIN.$canonicalUrl;
        $currentUrl   = '/' . implode('/', $segments);

        // 301 редирект, если текущий URL не совпадает с каноническим
        if (rtrim($currentUrl, '/') !== rtrim($canonicalUrl, '/')) {
            header("Location: $canonicalUrl", true, 301);
            exit;
        }

        //Хлебные крошки
        $url = 'https://'.HOME_DOMAIN;
        $breadcrumbsArr = [
            'Главная' => $url,
        ];
        foreach ($context as $value) {
            if(!$value) continue;
            $url .= '/'.$value->slug;
            $breadcrumbsArr[$value->name] = $url;
        }

        //лимит стихов на страницу
        $limit = 20;
        //сортировка значение
        $sort_value = request()->get('sort', $this->default_sort);
        if(!in_array($sort_value, $this->sort_values)) $sort_value = $this->default_sort; //валидация, установка по умолчанию

        //получаем номер страницы
        $page = request()->getNumberPage();

        $total_count = \App\Models\Poem::getCountByContext($context, true);
        $meta_desc .= " Всего: $total_count ".pluralize($total_count, 'стих', 'стиха', 'стихов').'.';
        // Вычисляем максимальный номер страницы
        $max_page = $total_count > 0 ? ceil($total_count / $limit) : 1;
        // Если запрошенная страница больше максимальной, сбрасываем на первую
        if ($page > $max_page) $page = 1;
        // Вычисляем offset
        $offset = ($page - 1) * $limit;
        if($page > 1) $meta_title .= ' - страница '.$page;


        // Ищем стихи по контексту
        $poems = \App\Models\Poem::getForCatalogPage($context, $limit, $offset, $sort_value, true);
        if($poems) {
            $last_poem = $poems[0];
            $this->view->sendHeadersLastUpdate($last_poem->add_at);
        }

        //пагинация
        $baseURL = $canonical;
        //добавляем сортировку в пагинацию
        if($sort_value != $this->default_sort) $baseURL .= '?sort='.$sort_value;
        $pagConfig = array(
            'baseURL' => $baseURL,
            'totalRows' => $total_count,
            'perPage' => $limit
        );
        $pagination =  new \App\Services\Pagination($pagConfig);         //инициализируем класс pagination
        $pagination_html = $pagination->createLinks();

        $filter_array = $this->getFilterArray($context, $repos);


        //Последние записи в блоге
        $posts = \App\Models\BlogPost::getLimitByOrderId(3,'DESC', true);
        $blog_posts_block = $this->renderBlock('blocks/blog_posts', ['posts' => $posts]);

        $safe_title = htmlspecialchars($meta_title);
        $safe_desc = htmlspecialchars($meta_desc);

        // Формируем ссылку на изображение
        $event_id = $context['event']?->id ?? 0;
        $encoded = $this->encodeContext($context);
        $width = 1200; // или 630 для превью

        $imagePath = "/images/catalog/{$event_id}/{$width}_{$encoded}.webp";
        $og_image = 'https://' . HOME_DOMAIN . $imagePath;

        // Формируем HTML строку для dop_head
        $dop_head = <<<HTML
<meta property="og:title" content="{$safe_title}">
<meta property="og:description" content="{$safe_desc}">
<meta property="og:type" content="website">
<meta property="og:url" content="{$canonical}">
<meta property="og:image" content="{$og_image}">
<meta property="og:site_name" content="ВСтихах.Ру">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{$safe_title}">
<meta name="twitter:description" content="{$safe_desc}">
<meta name="twitter:image" content="{$og_image}">
HTML;

        $bottom_text = $this->buildBottomText($context);

        // Рендерим страницу
        $this->render('pages/catalog', [
            'meta_title' => $meta_title,
            'meta_desc' => $meta_desc,
            'page_title' => $page_title,
            'poems' => $poems,
            'context' => $context,
            'canonical' => $canonical,
            'breadcrumbs_html' => $this->renderBlock('blocks/breadcrumbs', ['breadcrumbsArr' => $breadcrumbsArr]),
            'breadcrumbs_json' => $this->renderBlock('blocks/breadcrumbs_json', ['breadcrumbsArr' => $breadcrumbsArr]),
            'pagination_html' => $pagination_html,
            'filter_array' => $filter_array,
            'current_sort_value' => $sort_value,
            'sort_names' => $this->sort_names,
            'blog_posts_block' => $blog_posts_block,
            'dop_head' => $dop_head,
            'total_count' => $total_count,
            'bottom_text' => $bottom_text
        ]);
    }
    /**
     * Формирует массив данных для отображения блока фильтров на странице каталога.
     *
     * Для каждого типа фильтра (в порядке self::CANONICAL_ORDER):
     * 1. Перебирает все варианты из репозитория
     * 2. Для каждого варианта считает количество стихов через Poem::getCountByContext() с кэшированием
     * 3. Пропускает варианты с нулевым количеством стихов
     * 4. Определяет, активен ли текущий вариант в контексте страницы
     * 5. Формирует ссылку:
     *    - Если вариант активен → ссылка убирает этот фильтр из контекста
     *    - Если не активен → ссылка добавляет/заменяет этот фильтр
     * 6. Сортирует варианты: сначала активный (если есть), затем по имени (strcoll)
     *
     * @param array $context Текущий контекст страницы [тип => объект_модели | null]
     * @param array $repos Репозитории справочников [тип => [slug => объект_модели]]
     *
     * @return array Структура для вывода фильтра:
     *   [
     *     'event' => [
     *       ['name' => '...', 'count' => 42, 'href' => '/...', 'is_active' => true],
     *       ...
     *     ],
     *     ...
     *   ]
     */
    private function getFilterArray(array $context, array $repos): array
    {
        $db = \App\Core\Application::$app->db;
        $result = [];

        // Проходим по каждому типу фильтра в каноническом порядке
        foreach (self::CANONICAL_ORDER as $type) {
            $items = [];
            $repo = $repos[$type] ?? [];

            // Если в репозитории нет данных — пропускаем тип
            if (empty($repo)) {
                continue;
            }

            foreach ($repo as $slug => $entity) {
                // === 1. Считаем количество стихов для этого варианта фильтра ===
                // Строим тестовый контекст: текущий контекст, но с этим entity для текущего типа
                $testContext = $context;
                $testContext[$type] = $entity;

                // Считаем стихи (с кэшированием, чтобы не грузить БД при каждом клике)
                $count = \App\Models\Poem::getCountByContext($testContext, true);

                // Опционально: скрывать варианты, где 0 стихов
                if ($count === 0) continue;

                // === 2. Определяем, активен ли этот вариант сейчас ===
                $isActive = (
                    $context[$type] !== null &&
                    $context[$type]->id === $entity->id
                );

                // === 3. Формируем ссылку ===
                if ($isActive) {
                    // Если вариант активен — клик по нему УБИРАЕТ этот фильтр
                    $newContext = $context;
                    $newContext[$type] = null;
                } else {
                    // Если не активен — клик ДОБАВЛЯЕТ/ЗАМЕНЯЕТ этот фильтр
                    $newContext = $context;
                    $newContext[$type] = $entity;
                }

                // Строим канонический URL для нового контекста
                $href = $this->buildCanonicalUrl($newContext);

                // === 4. Добавляем в результат ===
                $items[] = [
                    'name' => $entity->name,
                    'count' => $count,
                    'href' => $href,
                    'is_active' => $isActive,
                ];
            }

            // Сортируем элементы: сначала активный (если есть), потом по имени
            usort($items, function($a, $b) {
                if ($a['is_active'] && !$b['is_active']) return -1;
                if (!$a['is_active'] && $b['is_active']) return 1;
                return strcoll($a['name'], $b['name']);
            });

            $result[$type] = $items;
        }

        return $result;
    }

    /**
     * Распознаёт сегменты пути и сопоставляет их с типами сущностей.
     *
     * Алгоритм:
     * 1. Инициализирует контекст: [тип => null] для всех типов из self::CANONICAL_ORDER
     * 2. Для каждого сегмента:
     *    - Перебирает типы в строгом порядке CANONICAL_ORDER
     *    - Пропускает типы, которые уже заняты в контексте
     *    - Если сегмент найден в репозитории типа — присваивает объект в контекст
     *    - Если ни в одном репозитории не найден — выбрасывает NotFoundException
     *
     * Пример возврата:
     *   [
     *     'event' => EventObject,
     *     'recipient' => null,
     *     'name' => NameObject,
     *     'genre' => null
     *   ]
     *
     * @param array $segments Массив сегментов пути из URL
     * @param array $repos Репозитории справочников [тип => [slug => объект]]
     *
     * @return array Контекст страницы [тип => объект_модели | null]
     *
     * @throws NotFoundException Если сегмент не найден ни в одном справочнике
     */
    private function identifySegments(array $segments, array $repos): array
    {
        // Инициализируем массив с null-значениями
        $context = array_fill_keys(self::CANONICAL_ORDER, null);

        foreach ($segments as $slug) {
            $found = false;

            // Ищем в строгом порядке канонической иерархии
            foreach (self::CANONICAL_ORDER as $type) {
                // Если этот тип уже занят другим сегментом, пропускаем
                if ($context[$type] !== null) {
                    continue;
                }

                if (isset($repos[$type][$slug])) {
                    $context[$type] = $repos[$type][$slug]; // Присваиваем объект модели
                    $found = true;
                    break;
                }
            }

            // Если ни в одном репозитории не нашли совпадение
            if (!$found) {
                throw new NotFoundException("Не нашли slug: {$slug}");
            }
        }

        return $context;
    }

    /**
     * Строит канонический URL из контекста страницы.
     *
     * Проходит по типам в порядке self::CANONICAL_ORDER,
     * добавляет в путь slug только тех сущностей, которые не null.
     *
     * Пример:
     *   Контекст: ['event' => {slug:'birthday'}, 'name' => {slug:'anna'}]
     *   Результат: '/birthday/anna'
     *
     * @param array $context Контекст страницы [тип => объект | null]
     *
     * @return string Путь относительно корня сайта, начинающийся с '/'
     */
    private function buildCanonicalUrl(array $context): string
    {
        $parts = [];
        foreach (self::CANONICAL_ORDER as $type) {
            if ($context[$type] !== null) {
                $parts[] = $context[$type]->slug;
            }
        }
        return '/' . implode('/', $parts);
    }


    /**
     * Генерирует мета-тег Title для поисковых систем.
     *
     * Порядок добавления фраз:
     * 1. Базовая часть: 'Поздравления в стихах'
     * 2. В порядке: recipient → name → event → genre
     *    - Использует поле 'phrase' если заполнено, иначе 'name'
     * 3. Добавляет суффикс: ' | {SITE_NAME}' (или 'Vstihah' если константа не определена)
     * 4. Обрезает до 70 символов если превышает лимит
     *
     * @param array $context Контекст страницы
     *
     * @return string Готовый мета-тайтл
     */
    private function buildMetaTitle(array $context): string
    {
        $parts = ['Поздравления в стихах'];

        // Порядок для title: recipient → name → event → genre
        $order = ['recipient', 'name', 'event', 'genre'];

        foreach ($order as $key) {
            if ($context[$key] !== null) {
                // Берём phrase, если есть, иначе name
                $phrase = !empty($context[$key]->phrase)
                    ? $context[$key]->phrase
                    : $context[$key]->name;
                $parts[] = $phrase;
            }
        }

        $title = implode(' ', $parts);

        // Ограничение длины для SEO (опционально)
        if (mb_strlen($title) > 70) {
            $title = mb_substr($title, 0, 67) . '...';
        }

        return $title . ' | ' . (defined('SITE_NAME') ? SITE_NAME : 'Vstihah');
    }

    /**
     * Генерирует заголовок H1 для отображения на странице.
     *
     * Порядок добавления фраз:
     * 1. Базовая часть: 'Стихи поздравления'
     * 2. В порядке: genre → event → recipient → name
     *    - Использует поле 'phrase' если заполнено, иначе 'name'
     *
     * @param array $context Контекст страницы
     *
     * @return строка Заголовок для вывода в шаблоне
     */
    private function buildPageTitle(array $context): string
    {
        $parts = ['Стихи поздравления'];

        // Порядок для H1: genre → event → recipient → name
        $order = ['genre', 'event', 'recipient', 'name'];

        foreach ($order as $key) {
            if ($context[$key] !== null) {
                $phrase = !empty($context[$key]->phrase)
                    ? $context[$key]->phrase
                    : $context[$key]->name;
                $parts[] = $phrase;
            }
        }

        return implode(' ', $parts);
    }

    /**
     * Генерирует мета-тег Description для поисковых сниппетов.
     *
     * Порядок добавления фраз:
     * 1. Базовая часть: 'Стихи'
     * 2. Если есть жанр — добавляет его фразу
     * 3. Добавляет 'для поздравления'
     * 4. В порядке: event → recipient → name добавляет фразы
     * 5. Добавляет точку в конце
     * 6. Обрезает до ~160 символов если превышает лимит
     *
     * @param array $context Контекст страницы
     *
     * @return string Готовое описание для мета-тега
     */
    /**
     * Генерирует уникальный и объемный мета-тег Description для поисковых сниппетов.
     *
     * @param array $context Контекст страницы
     * @return string Готовое описание для мета-тега
     */
    private function buildMetaDesc(array $context): string
    {
        // 1. Формируем "ядро" фразы на основе контекста
        $parts = ['стихи'];

        // Добавляем жанр
        if ($context['genre'] !== null) {
            $parts[] = !empty($context['genre']->phrase)
                ? $context['genre']->phrase
                : mb_strtolower($context['genre']->name);
        }

        // Если есть повод, получатель или имя, добавляем связку
        if ($context['event'] !== null || $context['recipient'] !== null || $context['name'] !== null) {
            $parts[] = 'для поздравления';
        }

        // Добавляем остальные сущности в правильном падеже (используем phrase)
        $order = ['event', 'recipient', 'name'];
        foreach ($order as $key) {
            if ($context[$key] !== null) {
                $parts[] = !empty($context[$key]->phrase)
                    ? $context[$key]->phrase
                    : mb_strtolower($context[$key]->name);
            }
        }

        $core = implode(' ', $parts);


        // 2. Готовим 10 вариантов шаблонов
        // В {core} подставится фраза вида:
        // "стихи в душевном стиле для поздравления на день рождения для мамы с именем Анна"
        $templates = [
            "Ищете $core? У нас собрана огромная коллекция лучших авторских произведений. Выбирайте и радуйте близких искренними словами!",
            "Самые красивые $core. Прочитайте и подберите идеальные строки, чтобы точно передать свои чувства и сделать праздник незабываемым.",
            "Большой выбор: $core. Отправляйте самые теплые и душевные пожелания прямо сейчас! Заходите и выбирайте идеальный текст.",
            "Нужны $core? В нашем каталоге найдутся оригинальные варианты для праздника. Удивите дорогих людей трогательным поздравлением!",
            "Искренние $core помогут подарить улыбку и искреннюю радость. Только качественные, ритмичные и проверенные тексты!",
            "Поздравляйте красиво! Лучшие $core бережно собраны в этом разделе. Найдите идеальный вариант из нашей огромной базы.",
            "Уникальные $core, написанные с душой. Найдите именно те слова, которые тронут до глубины души и подарят яркие эмоции.",
            "Отличная подборка: $core. От вечной классики до современности — у нас гарантированно найдутся подходящие строки для каждого.",
            "Не знаете, как выразить чувства? Выбирайте $core из нашего каталога. Удивите дорогих вам людей прекрасным слогом!",
            "Оригинальные $core сделают любое событие чуточку ярче. Вдохновляйтесь нашими авторскими текстами и дарите тепло."
        ];

        // 3. Вычисляем детерминированный индекс шаблона на основе контекста
        $hashString = '';
        foreach (self::CANONICAL_ORDER as $type) {
            if ($context[$type] !== null) {
                $hashString .= $context[$type]->slug;
            }
        }

        // Если есть контекст, генерируем индекс, если нет - берем нулевой шаблон
        $templateIndex = 0;
        if ($hashString !== '') {
            // crc32 выдает число. Берем модуль от деления на кол-во шаблонов.
            $templateIndex = abs(crc32($hashString)) % count($templates);
        }

        $desc = $templates[$templateIndex];

        // Ограничение длины ~170 символов для сниппета (Google сейчас отображает до 160-170 на мобильных, и больше на десктопе)
        // Если обрезаем, то аккуратно добавляем троеточие
//        if (mb_strlen($desc) > 170) {
//            $desc = mb_substr($desc, 0, 167) . '...';
//        }

        return $desc;
    }

    /**
     * Генерирует уникальный приветственный/SEO текст с эмодзи для вывода внизу страницы каталога.
     * Не имеет ограничений по длине и привязывается к контексту через crc32.
     *
     * @param array $context Контекст страницы
     * @return string Готовый HTML/текст
     */
    private function buildBottomText(array $context): string
    {
        // 1. Формируем "ядро" фразы на основе контекста
        $parts = ['стихи'];

        // Жанр
        if ($context['genre'] !== null) {
            $parts[] = !empty($context['genre']->phrase)
                ? $context['genre']->phrase
                : mb_strtolower($context['genre']->name);
        }

        // Связка, если есть сущности помимо жанра
        if ($context['event'] !== null || $context['recipient'] !== null || $context['name'] !== null) {
            $parts[] = 'для поздравления';
        }

        // Добавляем остальные сущности
        $order = ['event', 'recipient', 'name'];
        foreach ($order as $key) {
            if ($context[$key] !== null) {
                $parts[] = !empty($context[$key]->phrase)
                    ? $context[$key]->phrase
                    : mb_strtolower($context[$key]->name);
            }
        }

        $core = implode(' ', $parts);

        // 2. Шаблоны текстов для подвала с эмодзи (без ограничений по длине)
        $templates = [
            "На этой странице мы бережно собрали для вас $core. 🌟 Надеемся, что наши авторские строки помогут вам выразить свои самые искренние чувства и подарят радость вашим близким! ✨",
            "Ищете вдохновение? 🕊️ В нашем каталоге представлены лучшие $core. Выбирайте подходящее произведение, отправляйте его в мессенджерах или зачитывайте лично — теплые эмоции гарантированы! 💌",
            "Порой так сложно подобрать правильные слова... 💭 Именно поэтому мы подготовили для вас $core. Пусть каждая строчка будет наполнена искренностью, любовью и светом! 💖",
            "Рады приветствовать вас в этом разделе, где собраны $core! 💐 Мы регулярно пополняем нашу поэтическую коллекцию, чтобы вы могли легко найти идеальное поздравление для любого случая. 🎁",
            "Хотите сделать приятный сюрприз? 🎀 Выбрать и подарить $core — это замечательный способ проявить внимание и заботу. Читайте, выбирайте и дарите искренние улыбки! 😊",
            "Поэзия — прекрасный язык для выражения чувств. 🖋️ Здесь вы найдете $core, которые написаны с настоящей душой. Пусть ваш праздник станет еще ярче и теплее! 🥂",
            "Слова имеют огромную силу! 💫 Представляем вашему вниманию $core. Мы абсолютно уверены, что эти строки помогут создать неповторимую атмосферу уюта и настоящего праздника. 🏡",
            "Не упустите возможность порадовать дорогих вам людей! 🌸 Уникальные $core уже ждут вас. Просто скопируйте понравившийся текст и отправьте его виновнику торжества прямо сейчас! 📱",
            "Как прекрасно дарить радость! 🎉 В этой подборке вы найдете $core, которые идеально подойдут для вашего случая. Желаем приятного чтения и отличного праздничного настроения! 🎈",
            "Добро пожаловать в нашу творческую коллекцию! 📚 Специально для вас мы подобрали $core. Пусть эти трогательные пожелания согреют сердца тех, кто вам по-настоящему дорог. ❤️"
        ];

        // 3. Вычисляем детерминированный индекс шаблона на основе контекста
        $hashString = '';
        foreach (self::CANONICAL_ORDER as $type) {
            if ($context[$type] !== null) {
                $hashString .= $context[$type]->slug;
            }
        }

        // Вычисляем индекс от 0 до 9
        $templateIndex = abs(crc32($hashString)) % count($templates);

        return $templates[$templateIndex];
    }
}