<?php
$firstDir = '';
if (function_exists('request')) {
    $firstDir = trim((string) request()->getFirstDir(), '/');
}

$isActiveUrl = function (string $url) use ($firstDir): bool {
    $urlDir = trim($url, '/');
    return $urlDir !== '' && $urlDir === $firstDir;
};

$hasActiveItems = function (array $items) use (&$hasActiveItems, $isActiveUrl): bool {
    foreach ($items as $item) {
        if (isset($item['url']) && $isActiveUrl($item['url'])) {
            return true;
        }

        if (!empty($item['children']) && $hasActiveItems($item['children'])) {
            return true;
        }
    }

    return false;
};

$menu = [
    [
        'title' => 'Праздники',
        'children' => [
            [
                'title' => 'Праздник',
                'children' => [
                    ['title' => 'Старый Новый год', 'url' => '/staryy-novyy-god'],
                    ['title' => 'Новый год', 'url' => '/noviy-god'],
                    ['title' => 'День всех влюблённых', 'url' => '/den-vsekh-vlyublyonnykh'],
                    ['title' => 'День благодарения', 'url' => '/den-blagodareniya'],
                    ['title' => 'Пасха', 'url' => '/paskha'],
                    ['title' => 'Масленица', 'url' => '/maslenitsa'],
                    ['title' => 'День Ивана Купалы', 'url' => '/den-ivana-kupaly'],
                    ['title' => 'День студента', 'url' => '/den-studenta'],
                    ['title' => 'День космонавтики', 'url' => '/den-kosmonavtiki'],
                    ['title' => 'День дружбы', 'url' => '/den-druzhby'],
                    ['title' => '23 февраля', 'url' => '/23-fevralya'],
                    ['title' => '8 марта', 'url' => '/8-marta'],
                ],
            ],
            [
                'title' => 'Семейный',
                'children' => [
                    ['title' => 'День матери', 'url' => '/den-materi'],
                    ['title' => 'День отца', 'url' => '/den-ottsa'],
                    ['title' => 'Годовщина свадьбы', 'url' => '/godovshchina-svadby'],
                    ['title' => 'Свадьба', 'url' => '/svadba'],
                    ['title' => 'День бабушки', 'url' => '/den-babushki'],
                    ['title' => 'День дедушки', 'url' => '/den-dedushki'],
                    ['title' => 'День семьи', 'url' => '/den-semi'],
                    ['title' => 'Рождение ребенка', 'url' => '/rozhdenie-rebyonka'],
                    ['title' => 'Крестины', 'url' => '/krestiny'],
                ],
            ],
            [
                'title' => 'Личная дата',
                'children' => [
                    ['title' => 'День рождения', 'url' => '/den-rozhdeniya'],
                    ['title' => 'Юбилей', 'url' => '/yubiley'],
                    ['title' => 'Юбилей 30 лет', 'url' => '/yubiley-30-let'],
                    ['title' => 'Юбилей 40 лет', 'url' => '/yubiley-40-let'],
                    ['title' => 'Юбилей 50 лет', 'url' => '/yubiley-50-let'],
                    ['title' => 'Юбилей 60 лет', 'url' => '/yubiley-60-let'],
                    ['title' => 'Выпускной', 'url' => '/vypusknoy'],
                    ['title' => 'Новоселье', 'url' => '/novoselye'],
                    ['title' => 'Беременность', 'url' => '/beremennost'],
                    ['title' => 'Покупка машины', 'url' => '/pokupka-mashiny'],
                    ['title' => 'Получение водительских прав', 'url' => '/poluchenie-voditelskikh-prav'],
                    ['title' => 'Победа на соревнованиях', 'url' => '/pobeda-na-sorevnovaniyakh'],
                ],
            ],
            [
                'title' => 'Профессиональный',
                'children' => [
                    ['title' => 'День программиста', 'url' => '/den-programmista'],
                    ['title' => 'День фрилансера', 'url' => '/den-frilansera'],
                    ['title' => 'День блогера', 'url' => '/den-blogera'],
                    ['title' => 'Новая работа', 'url' => '/novaya-rabota'],
                    ['title' => 'Повышение', 'url' => '/povyshenie'],
                    ['title' => 'Открытие бизнеса', 'url' => '/otkrytie-biznesa'],
                ],
            ],
        ],
    ],
    [
        'title' => 'Получатель',
        'children' => [
            [
                'title' => 'Родственник',
                'children' => [
                    ['title' => 'Мама', 'url' => '/mama'],
                    ['title' => 'Папа', 'url' => '/papa'],
                    ['title' => 'Бабушка', 'url' => '/babushka'],
                    ['title' => 'Дедушка', 'url' => '/dedushka'],
                    ['title' => 'Сын', 'url' => '/syn'],
                    ['title' => 'Дочь', 'url' => '/doch'],
                    ['title' => 'Брат', 'url' => '/brat'],
                    ['title' => 'Сестра', 'url' => '/sestra'],
                    ['title' => 'Муж', 'url' => '/muzh'],
                    ['title' => 'Жена', 'url' => '/zhena'],
                    ['title' => 'Тётя', 'url' => '/tetya'],
                    ['title' => 'Дядя', 'url' => '/dyadya'],
                    ['title' => 'Внук', 'url' => '/vnuk'],
                    ['title' => 'Внучка', 'url' => '/vnuchka'],
                ],
            ],
            [
                'title' => 'Социальный статус',
                'children' => [
                    ['title' => 'Друг', 'url' => '/drug'],
                    ['title' => 'Подруга', 'url' => '/podruga'],
                    ['title' => 'Коллега', 'url' => '/kollega'],
                    ['title' => 'Начальник', 'url' => '/nachalnik'],
                    ['title' => 'Учитель', 'url' => '/uchitel'],
                    ['title' => 'Врач', 'url' => '/vrach'],
                    ['title' => 'Сосед', 'url' => '/sosed'],
                    ['title' => 'Соседка', 'url' => '/sosedka'],
                    ['title' => 'Классный руководитель', 'url' => '/klassnyy-rukovoditel'],
                    ['title' => 'Тренер', 'url' => '/trener'],
                    ['title' => 'Подчинённый', 'url' => '/podchinenniy'],
                ],
            ],
        ],
    ],
    [
        'title' => 'Имена',
        'children' => [
            [
                'title' => 'Мужские',
                'children' => [
                    ['title' => 'Михаил', 'url' => '/mikhail'],
                    ['title' => 'Александр', 'url' => '/aleksandr'],
                    ['title' => 'Артём', 'url' => '/artyom'],
                    ['title' => 'Матвей', 'url' => '/matvey'],
                    ['title' => 'Тимофей', 'url' => '/timofey'],
                    ['title' => 'Максим', 'url' => '/maksim'],
                    ['title' => 'Лев', 'url' => '/lev'],
                    ['title' => 'Марк', 'url' => '/mark'],
                    ['title' => 'Дмитрий', 'url' => '/dmitriy'],
                    ['title' => 'Иван', 'url' => '/ivan'],
                    ['title' => 'Кирилл', 'url' => '/kirill'],
                    ['title' => 'Никита', 'url' => '/nikita'],
                    ['title' => 'Илья', 'url' => '/ilya'],
                    ['title' => 'Андрей', 'url' => '/andrey'],
                    ['title' => 'Алексей', 'url' => '/aleksey'],
                    ['title' => 'Роман', 'url' => '/roman'],
                    ['title' => 'Сергей', 'url' => '/sergey'],
                    ['title' => 'Владислав', 'url' => '/vladislav'],
                    ['title' => 'Константин', 'url' => '/konstantin'],
                    ['title' => 'Павел', 'url' => '/pavel'],
                    ['title' => 'Денис', 'url' => '/denis'],
                    ['title' => 'Арсений', 'url' => '/arseniy'],
                    ['title' => 'Егор', 'url' => '/egor'],
                    ['title' => 'Даниил', 'url' => '/daniil'],
                    ['title' => 'Фёдор', 'url' => '/fedor'],
                ],
            ],
            [
                'title' => 'Женские',
                'children' => [
                    ['title' => 'София', 'url' => '/sofiya'],
                    ['title' => 'Ева', 'url' => '/eva'],
                    ['title' => 'Анна', 'url' => '/anna'],
                    ['title' => 'Мария', 'url' => '/mariya'],
                    ['title' => 'Варвара', 'url' => '/varvara'],
                    ['title' => 'Виктория', 'url' => '/viktoriya'],
                    ['title' => 'Василиса', 'url' => '/vasilisa'],
                    ['title' => 'Полина', 'url' => '/polina'],
                    ['title' => 'Александра', 'url' => '/aleksandra'],
                    ['title' => 'Елизавета', 'url' => '/elizaveta'],
                    ['title' => 'Дарья', 'url' => '/darya'],
                    ['title' => 'Ксения', 'url' => '/kseniya'],
                    ['title' => 'Валерия', 'url' => '/valeriya'],
                    ['title' => 'Анастасия', 'url' => '/anastasiya'],
                    ['title' => 'Милана', 'url' => '/milana'],
                    ['title' => 'Алиса', 'url' => '/alisa'],
                    ['title' => 'Яна', 'url' => '/yana'],
                    ['title' => 'Вероника', 'url' => '/veronika'],
                    ['title' => 'Ольга', 'url' => '/olga'],
                    ['title' => 'Екатерина', 'url' => '/ekaterina'],
                    ['title' => 'Наталья', 'url' => '/nataliya'],
                    ['title' => 'Юлия', 'url' => '/yuliya'],
                    ['title' => 'Светлана', 'url' => '/svetlana'],
                    ['title' => 'Ирина', 'url' => '/irina'],
                    ['title' => 'Татьяна', 'url' => '/tatyana'],
                ],
            ],
        ],
    ],
    [
        'title' => 'Жанры',
        'children' => [
            ['title' => 'Лирический', 'url' => '/liricheskiy'],
            ['title' => 'Юмористический', 'url' => '/yumoristicheskiy'],
            ['title' => 'Официальный', 'url' => '/ofitsialnyy'],
            ['title' => 'Романтический', 'url' => '/romanticheskiy'],
            ['title' => 'Тост', 'url' => '/tost'],
            ['title' => 'Душевный', 'url' => '/dushevnyy'],
            ['title' => 'Весёлый', 'url' => '/veselyy'],
            ['title' => 'Нежный', 'url' => '/nezhnyy'],
            ['title' => 'Дружеский', 'url' => '/druzheskiy'],
        ],
    ],
        [
            'title' => 'Блог',
            'url' => '/blog'
        ],
];

$renderMenuItems = function (array $items, int $level = 0) use (&$renderMenuItems, $isActiveUrl, $hasActiveItems): void {
    foreach ($items as $item) {
        $hasChildren = !empty($item['children']);
        $isActive = isset($item['url']) && $isActiveUrl($item['url']);
        $isParentActive = $hasChildren && $hasActiveItems($item['children']);

        $liClass = 'site-nav__item';
        if ($hasChildren) {
            $liClass .= ' has-dropdown';
        }
        if ($isActive || $isParentActive) {
            $liClass .= ' is-active';
        }

        $linkClass = 'site-nav__link';
        if ($level > 0) {
            $linkClass .= ' site-nav__dropdown-link';
        }
        if ($isActive || $isParentActive) {
            $linkClass .= ' is-active';
        }

        echo '<li class="' . htmlspecialchars($liClass, ENT_QUOTES) . '">';

        $title = htmlspecialchars($item['title'], ENT_QUOTES);
        $url = isset($item['url']) ? htmlspecialchars($item['url'], ENT_QUOTES) : '#';

        echo '<a class="' . htmlspecialchars($linkClass, ENT_QUOTES) . '" href="' . $url . '">';
        echo '<span>' . $title . '</span>';

        if ($hasChildren) {
            echo '<span class="site-nav__chevron" aria-hidden="true"></span>';
        }

        echo '</a>';

        if ($hasChildren) {
            $dropdownClass = $level === 0 ? 'site-nav__dropdown' : 'site-nav__dropdown site-nav__dropdown--sub';
            echo '<ul class="' . $dropdownClass . '">';
            $renderMenuItems($item['children'], $level + 1);
            echo '</ul>';
        }

        echo '</li>';
    }
};
?>
<header class="site-header">
    <div class="site-header__inner">
        <div class="mobile-bar">
            <a class="site-logo" href="/" aria-label="ВСтихах.Ру">
                <img src="/images/logo.svg" class="site-logo__mark" alt="Встихах.ру - стихи поздравления" />
                <span>В<span class="site-logo__accent">Стихах</span>.Ру</span>
            </a>

            <button class="nav-toggle" type="button" aria-label="Открыть меню" aria-expanded="false" aria-controls="siteNavWrap">
                <span class="nav-toggle__lines"></span>
            </button>
        </div>

        <div class="site-header__top">
            <a class="site-logo" href="/" aria-label="ВСтихах.Ру">
                <img src="/images/logo.svg" class="site-logo__mark" alt="Встихах.ру - стихи поздравления" />
                <span>В<span class="site-logo__accent">Стихах</span>.Ру</span>
            </a>

            <div class="site-header__lead">
                Красивые поздравления в стихах для праздников, близких людей, имён и особых случаев.
            </div>
        </div>

        <nav class="site-nav-wrap" id="siteNavWrap" aria-label="Главное меню">
            <ul class="site-nav">
                <?php $renderMenuItems($menu); ?>
            </ul>
        </nav>
    </div>
</header>