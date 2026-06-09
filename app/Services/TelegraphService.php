<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use \DOMDocument;
use \DOMNode;

class TelegraphService
{
    private readonly string $apiUrl;

    public function __construct()
    {
        $this->apiUrl = 'https://api.telegra.ph/';
    }

    public function getRandomTextFooter(): string
    {
        $arr = [
            '<p>Продлите новогоднее волшебство — душевные поздравления в стихах на <a href="https://vstihah.ru/staryy-novyy-god">старый новый год</a> уже ждут вас!</p>',
            '<p>Хотите растрогать маму до слёз? Найдите идеальные слова ко <a href="https://vstihah.ru/den-materi">дню матери</a> в нашей коллекции стихов.</p>',
            '<p>Папа — главный герой семьи! Подберите уважительное и тёплое поздравление на <a href="https://vstihah.ru/den-ottsa">день отца</a> в стихах.</p>',
            '<p>Готовитесь к главному празднику года? У нас есть всё: тосты, пожелания и стихи на <a href="https://vstihah.ru/noviy-god">новый год</a>!</p>',
            '<p>Признайтесь в любви красиво — романтичные стихи на <a href="https://vstihah.ru/den-vsekh-vlyublyonnykh">день всех влюблённых</a> помогут сказать самое важное.</p>',
            '<p>Иногда так важно сказать «спасибо» от всего сердца. Найдите тёплые слова в стихах на <a href="https://vstihah.ru/den-blagodareniya">день благодарения</a>.</p>',
            '<p>Светлый праздник требует светлых слов — поздравления в стихах на <a href="https://vstihah.ru/paskha">пасху</a> для самых близких.</p>',
            '<p>Проводите зиму с улыбкой и блинами! Весёлые стихи на <a href="https://vstihah.ru/maslenitsa">масленицу</a> для праздничного настроения.</p>',
            '<p>Загадочная ночь, костры и традиции — украсьте праздник красивыми стихами на <a href="https://vstihah.ru/den-ivana-kupaly">день ивана купалы</a>.</p>',
            '<p>Сессия позади, праздник впереди! Крутые поздравления в стихах на <a href="https://vstihah.ru/den-studenta">день студента</a> для одногруппников.</p>',
            '<p>Ищете оригинальное поздравление? У нас сотни уникальных стихов на <a href="https://vstihah.ru/den-rozhdeniya">день рождения</a> на любой характер и возраст.</p>',
            '<p>Год за годом вместе — это стоит отметить! Романтичные стихи на <a href="https://vstihah.ru/godovshchina-svadby">годовщину свадьбы</a> для ваших любимых.</p>',
            '<p>Самый важный день — самые красивые слова. Поздравления в стихах на <a href="https://vstihah.ru/svadba">свадьбу</a> для молодожёнов и гостей торжества.</p>',
            '<p>Бабушка — это любовь без границ и условий. Порадуйте её тёплыми стихами на <a href="https://vstihah.ru/den-babushki">день бабушки</a>.</p>',
            '<p>Дедушка — герой с седой бородой и золотым сердцем. Найдите душевное поздравление на <a href="https://vstihah.ru/den-dedushki">день дедушки</a>.</p>',
            '<p>Семья — это главное богатство. Объедините близких тёплыми словами в стихах на <a href="https://vstihah.ru/den-semi">день семьи</a>.</p>',
            '<p>Круглая дата — повод для особых, торжественных слов. Подберите идеальные стихи на <a href="https://vstihah.ru/yubiley">юбилей</a> для виновника праздника.</p>',
            '<p>30 лет — расцвет сил, энергии и возможностей! Яркие поздравления в стихах на <a href="https://vstihah.ru/yubiley-30-let">юбилей 30 лет</a>.</p>',
            '<p>40 лет — время мудрости, опыта и новых вершин. Найдите идеальные стихи на <a href="https://vstihah.ru/yubiley-40-let">юбилей 40 лет</a>.</p>',
            '<p>Полвека — это серьёзно и красиво! Торжественные поздравления в стихах на <a href="https://vstihah.ru/yubiley-50-let">юбилей 50 лет</a>.</p>',
            '<p>60 лет — золотой возраст мудрости и уважения. Подарите юбиляру красивые слова в стихах на <a href="https://vstihah.ru/yubiley-60-let">юбилей 60 лет</a>.</p>',
            '<p>Новая жизнь — новое счастье! Трогательные поздравления в стихах на <a href="https://vstihah.ru/rozhdenie-rebyonka">рождение ребенка</a> для молодых родителей.</p>',
            '<p>Школа позади, жизнь впереди! Мотивирующие и памятные стихи на <a href="https://vstihah.ru/vypusknoy">выпускной</a> для тех, кто делает первый взрослый шаг.</p>',
            '<p>Новый дом — новая глава жизни! Поздравьте близких уютными стихами на <a href="https://vstihah.ru/novoselye">новоселье</a>.</p>',
            '<p>Поехали! Отметьте день покорения космоса звёздными стихами на <a href="https://vstihah.ru/den-kosmonavtiki">день космонавтики</a>.</p>',
            '<p>0 и 1, а сколько в них чувств! Креативные поздравления в стихах на <a href="https://vstihah.ru/den-programmista">день программиста</a> для IT-героев.</p>',
            '<p>Свобода, гибкий график и кофе — с праздником! Найдите крутые стихи на <a href="https://vstihah.ru/den-frilansera">день фрилансера</a> для удалёнщиков.</p>',
            '<p>Лайки, репосты, вдохновение — с праздником! Яркие поздравления в стихах на <a href="https://vstihah.ru/den-blogera">день блогера</a> для создателей контента.</p>',
            '<p>Друзья — это семья по выбору. Порадуйте их тёплыми и искренними стихами на <a href="https://vstihah.ru/den-druzhby">день дружбы</a>.</p>',
            '<p>Светлое таинство — светлые слова. Душевные поздравления в стихах на <a href="https://vstihah.ru/krestiny">крестины</a> для малыша и его родителей.</p>',
            '<p>Новый старт — новые победы! Пожелайте удачи и вдохновения в стихах на <a href="https://vstihah.ru/novaya-rabota">новую работу</a>.</p>',
            '<p>Карьерный взлёт заслуживает оваций! Поздравления в стихах на <a href="https://vstihah.ru/povyshenie">повышение</a> для коллеги или руководителя.</p>',
            '<p>В ожидании чуда — самые нежные слова. Трогательные стихи на <a href="https://vstihah.ru/beremennost">беременность</a> для будущей мамы.</p>',
            '<p>Четыре колеса — новая свобода! Поздравьте с обновкой в стихах на <a href="https://vstihah.ru/pokupka-mashiny">покупку машины</a>.</p>',
            '<p>Права в кармане — дорога открыта! Весёлые и мотивирующие стихи на <a href="https://vstihah.ru/poluchenie-voditelskikh-prav">получение водительских прав</a>.</p>',
            '<p>Золото, кубок, слава — вы это заслужили! Поздравления в стихах на <a href="https://vstihah.ru/pobeda-na-sorevnovaniyakh">победу на соревнованиях</a> для чемпионов.</p>',
            '<p>Свой бизнес — своя история успеха! Пожелайте процветания в стихах на <a href="https://vstihah.ru/otkrytie-biznesa">открытие бизнеса</a>.</p>',
            '<p>Защитникам Отечества — самые мужественные слова. Поздравления в стихах на <a href="https://vstihah.ru/23-fevralya">23 февраля</a> для настоящих героев.</p>',
            '<p>Весна, цветы и комплименты — всё для неё! Романтичные стихи на <a href="https://vstihah.ru/8-marta">8 марта</a> для любимых женщин.</p>',
        ];
        return $arr[rand(0, count($arr) - 1)];
    }

    /**
     * 1. Создание аккаунта
     */
    public function createAccount(string $shortName, string $authorName = '', string $authorUrl = ''): array
    {
        return $this->request('createAccount', [
            'short_name'  => $shortName,
            'author_name' => $authorName,
            'author_url'  => $authorUrl,
        ]);
    }

    /**
     * 2 & 3. Создание страницы с картинкой и HTML контентом
     */
    public function publishPage(
        string $accessToken,
        string $title,
        string $htmlContent,
        ?string $topImageUrl = null,
        string $authorName = '',
        string $authorUrl = ''
    ): array {
        // Формируем базовый массив контента
        $contentNodes = [];

        // Добавляем картинку в начало, если она передана
        if ($topImageUrl !== null) {
            $contentNodes[] = [
                'tag' => 'figure',
                'children' => [
                    ['tag' => 'img', 'attrs' => ['src' => $topImageUrl]]
                ]
            ];
        }

        // Парсим HTML и добавляем к контенту
        $parsedHtmlNodes = $this->parseHtmlToTelegraphNodes($htmlContent);
        $contentNodes = array_merge($contentNodes, $parsedHtmlNodes);

        // Отправляем запрос
        return $this->request('createPage', [
            'access_token'   => $accessToken,
            'title'          => $title,
            'author_name'    => $authorName,
            'author_url'     => $authorUrl,
            'content'        => json_encode($contentNodes), // API требует JSON-строку для этого поля
            'return_content' => false
        ]);
    }

    /**
     * Выполнение cURL запроса к API
     */
    private function request(string $method, array $data): array
    {
        $ch = curl_init($this->apiUrl . $method);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $data, // Отправляем как multipart/form-data
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("Ошибка cURL: " . $error);
        }

        $result = json_decode($response, true);

        if (isset($result['ok']) && $result['ok'] === true) {
            return $result['result'];
        }

        $errorMessage = $result['error'] ?? 'Неизвестная ошибка API Telegra.ph';
        throw new Exception("Ошибка Telegraph API: " . $errorMessage);
    }

    /**
     * Конвертация сырого HTML в формат DOM-узлов Telegraph
     */
    private function parseHtmlToTelegraphNodes(string $html): array
    {
        if (trim($html) === '') {
            return [];
        }

        // Подавляем ошибки невалидного HTML
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        // Добавляем метатег и обертку, чтобы избежать проблем с кириллицей (UTF-8)
        $dom->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8"><div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $nodes = [];
        // Проходим по дочерним элементам нашего фейкового <div>
        foreach ($dom->documentElement->childNodes as $child) {
            $parsedNode = $this->convertNode($child);
            if ($parsedNode !== null) {
                $nodes[] = $parsedNode;
            }
        }

        return $nodes;
    }

    /**
     * Рекурсивный обход элементов DOM
     */
    private function convertNode(DOMNode $node): array|string|null
    {
        // Если это просто текст
        if ($node->nodeType === XML_TEXT_NODE) {
            $text = $node->textContent;
            return trim($text) === '' ? null : $text;
        }

        // Если это не HTML элемент
        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return null;
        }

        $tag = strtolower($node->nodeName);
        $allowedTags = ['a', 'aside', 'b', 'blockquote', 'br', 'code', 'em', 'figcaption', 'figure', 'h3', 'h4', 'hr', 'i', 'iframe', 'img', 'li', 'ol', 'p', 'pre', 's', 'strong', 'u', 'ul', 'video'];

        // Маппинг неподдерживаемых тегов в поддерживаемые
        $tagMap = [
            'div'  => 'p',
            'span' => 'p',
            'h1'   => 'h3',
            'h2'   => 'h3',
            'h5'   => 'h4',
            'h6'   => 'h4',
        ];
        $tag = $tagMap[$tag] ?? $tag;

        // Если тег всё равно не поддерживается, пытаемся вытащить его содержимое
        if (!in_array($tag, $allowedTags)) {
            $children = [];
            foreach ($node->childNodes as $child) {
                $parsed = $this->convertNode($child);
                if ($parsed !== null) $children[] = $parsed;
            }
            return empty($children) ? null : ['tag' => 'p', 'children' => $children];
        }

        $element = ['tag' => $tag];

        // Собираем разрешенные атрибуты (ссылки)
        if ($node->hasAttributes()) {
            $attrs = [];
            foreach ($node->attributes as $attr) {
                if (in_array($attr->name, ['href', 'src'])) {
                    $attrs[$attr->name] = $attr->value;
                }
            }
            if (!empty($attrs)) {
                $element['attrs'] = $attrs;
            }
        }

        // Собираем вложенные элементы
        if ($node->hasChildNodes()) {
            $children = [];
            foreach ($node->childNodes as $child) {
                $parsedChild = $this->convertNode($child);
                if ($parsedChild !== null) {
                    $children[] = $parsedChild;
                }
            }
            if (!empty($children)) {
                $element['children'] = $children;
            }
        }

        return $element;
    }
}