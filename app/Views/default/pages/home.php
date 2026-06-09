<section class="hero_block">
    <div class="hero_bg"></div>

    <div class="container">
        <div class="hero_inner">
            <div class="hero_content">
                <div class="hero_mark">слова, которые хочется сказать вслух</div>
                <h1>Поздравляйте в стихах</h1>
                <p class="lead">Долой дежурные фразы из открыток. Давайте честно: хорошие стихи — это не про «зазубрил и рассказал». Это про то, чтобы попасть в сердце, а не просто в рифму 💙</p>

                <div class="hero_actions">
                    <a href="#events" class="btn_main">Найти стих</a>
                    <a href="/blog" class="btn_plain">Почитать блог</a>
                </div>
            </div>

            <div class="hero_card">
                <span>для мамы</span>
                <span>на юбилей</span>
                <span>с юмором</span>
                <span>по имени</span>
                <span>для коллеги</span>
                <span>в тост</span>
            </div>
        </div>
    </div>
</section>

<section class="events_block home_section" id="events">
    <div class="container">
        <div class="section_grid">
            <div class="section_intro">
                <span class="section_kicker">Поводы</span>
                <h2>На любое событие</h2>
                <p class="section_big">Жизнь — это не только День рождения и Новый год. Хотя, признаем, их мы тоже любим ⚡</p>
            </div>

            <div class="section_text">
                <p>У нас есть стихи для моментов, которые часто остаются «за кадром»: новоселье, первая зарплата, покупка машины, повышение, крестины, беременность. И да, даже День программиста — мы помним, кто делает этот сайт возможным.</p>
                <p>Не нашли свой повод? Напишите нам. Серьёзно. Мы не боты, не корпорация, не отдел контента. Мы — люди, которые тоже когда-то искали нужные слова и не находили. Исправим это вместе.</p>
            </div>
        </div>

        <div class="links_panel links_panel_events">
            <?php foreach ($events as $event) {
                if(isset($event->count) && $event->count > 0){
                    echo '<a href="/'.$event->slug.'">'.$event->name.' <sup>'.$event->count.'</sup></a>';
                }

            }?>
        </div>
    </div>
</section>


<section class="recipients_block home_section">
    <div class="container">
        <div class="recipients_layout">
            <div class="recipients_note">
                <img src="/images/letter.svg" class="img-fluid m-2" alt="ВСтихах.Ру - Стихи для разных получателей.">
                <p>Слова для сестры и слова для тренера звучат по-разному. Поэтому лучше выбирать не только повод, но и человека.</p>
            </div>

            <div class="recipients_content">
                <span class="section_kicker">Получатели</span>
                <h2>Для самых важных людей</h2>
                <p class="section_big">Мама, папа, бабушка, друг, коллега, начальник — да, и для него тоже найдём что-то приличное.</p>
                <p>Подбирать стих по получателю — это как выбирать подарок: универсальный носок — это нормально, но тёплый шарф, связанный «под человека», греет иначе. Мы не делим людей на «важных» и «не очень». Но понимаем: слова для каждого человека должны звучать по-своему.</p>

                <div class="links_panel links_panel_recipients">
                    <?php foreach ($recipients as $recipient) {
                        if(isset($recipient->count) && $recipient->count > 0){
                            echo '<a href="/'.$recipient->slug.'">'.$recipient->name.' <sup>'.$recipient->count.'</sup></a>';
                        }

                    }?>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="names_block home_section">
    <div class="container">
        <div class="names_box">
            <div class="names_decor" aria-hidden="true">
                <span>А</span>
                <span>М</span>
                <span>С</span>
                <span>Е</span>
                <span>Н</span>
            </div>

            <div class="names_content">
                <span class="section_kicker">Персонально</span>
                <h2>Имена</h2>
                <p class="section_big">«Дорогой Александр» и «Дорогой Саша» — это два разных настроения.</p>
                <p>А если добавить отчество? А если имя редкое, и все стихи про «наших Тань» и «наших Серёж»?</p>
                <p>Мы собрали стихи по именам — не для галочки, а для того, чтобы человек услышал своё имя и почувствовал: «О, это точно про меня».</p>
                <p>Мужские, женские, короткие, развёрнутые, с акцентом на характер или на пожелание. И да, мы добавляем новые имена — если вашего нет, вы знаете, что делать 😉</p>

                <div class="links_panel links_panel_names">
                    <?php foreach ($names as $name) {
                        if(isset($name->count) && $name->count > 0){
                            $class = $name->gender == 'male' ? 'male' : 'female';
                            echo '<a href="/'.$name->slug.'" class="'.$class.'">'.$name->name.' <sup>'.$name->count.'</sup></a>';
                        }

                    }?>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="genres_block home_section">
    <div class="container">
        <div class="genres_header">
            <div>
                <span class="section_kicker">Настроение</span>
                <h2>Стили и жанры</h2>
            </div>
            <p>Выбирайте не только «про что», но и «как»: лирично, смешно, душевно, романтично или в формате тоста.</p>
        </div>

        <div class="links_panel links_panel_genres">
            <?php foreach ($genres as $genre) {
                if(isset($genre->count) && $genre->count > 0){
                    echo '<a href="/'.$genre->slug.'">'.$genre->name.' <sup>'.$genre->count.'</sup></a>';
                }

            }?>
        </div>
        <div class="genres_footer px-2 ">
            <p class="text-center">Хотите лирику до слёз? Или юмор, чтобы гости за столом запомнили ваш тост? А может, что-то официальное — для коллеги или руководителя, но без канцелярита? Совет от бывшего стесняшки: если не уверены в жанре — берите «душевный». Он, как джинсы, подходит почти ко всему 💡</p>
        </div>

    </div>
</section>

<section class="blog_block home_section">
    <div class="container">
        <div class="blog_layout">
            <div class="blog_intro">
                <span class="section_kicker">Блог</span>
                <h2>Есть же еще и блог у нас</h2>
                <p class="section_big">Иногда хочется не просто найти стих, а почитать о том, как их пишут, как выбирают и как не растеряться, когда нужно сказать вслух.</p>
                <p>В блоге — заметки без пафоса: про живые ситуации, про ошибки, которые мы все совершаем, и про то, почему иногда одно четверостишие значит больше, чем длинная речь.</p>
                <p>Заходите, когда есть минутка. Мы не требуем подписки и не шлём уведомления в 3 ночи. Просто делимся тем, что накопили. Ну пожалуйста, мы ведь старались.</p>

                <p class="blog_more">
                    <a href="/blog">Смотреть все записи</a>
                </p>
            </div>

            <div class="blog_posts_wrap blog_home">
                <?php echo $blog_posts_block;?>
            </div>
        </div>
    </div>
</section>

<style>
    .hero_block {
        position: relative;
        overflow: hidden;
        min-height: 620px;
        display: flex;
        align-items: center;
        padding: 86px 0 72px;
        isolation: isolate;
    }

    .hero_bg {
        position: absolute;
        inset: 0;
        z-index: -2;
        background:
                radial-gradient(circle at 12% 20%, rgba(230, 106, 92, .20), transparent 34%),
                radial-gradient(circle at 86% 16%, rgba(115, 87, 216, .18), transparent 32%),
                linear-gradient(135deg, #fff8f4 0%, #faf7f5 48%, #f7f5ff 100%);
    }

    .hero_bg::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image: url("/images/hero-pattern.webp");
        background-size: 620px auto;
        background-position: center;
        opacity: .16;
        mix-blend-mode: multiply;
    }

    .hero_bg::after {
        content: "";
        position: absolute;
        width: 520px;
        height: 520px;
        right: -140px;
        bottom: -190px;
        border-radius: 50%;
        background: conic-gradient(from 120deg, rgba(230, 106, 92, .22), rgba(115, 87, 216, .20), rgba(230, 106, 92, .22));
        filter: blur(8px);
        animation: heroFloat 12s ease-in-out infinite alternate;
    }

    .hero_inner {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 360px;
        gap: 54px;
        align-items: center;
    }

    .hero_content {
        max-width: 780px;
    }

    .hero_mark {
        display: inline-block;
        margin-bottom: 18px;
        color: var(--accent);
        font-size: 28px;
        line-height: 1;
        font-family: "Neucha", sans-serif;
        transform: rotate(-2deg);
    }

    .hero_block h1 {
        max-width: 760px;
        margin: 0;
        font-size: clamp(46px, 7vw, 92px);
        line-height: .92;
        letter-spacing: -.065em;
        color: var(--text);
    }

    .hero_block h1::after {
        content: "";
        display: block;
        width: min(320px, 62%);
        height: 12px;
        margin-top: 16px;
        border-radius: 999px;
        background: linear-gradient(90deg, var(--accent), var(--accent-2));
        opacity: .88;
    }

    .hero_block .lead {
        max-width: 720px;
        margin: 28px 0 0;
        color: #444a58;
        font-size: clamp(18px, 2vw, 23px);
        line-height: 1.55;
    }

    .hero_actions {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        margin-top: 34px;
    }

    .btn_main,
    .btn_plain {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 48px;
        padding: 13px 22px;
        border-radius: 999px;
        text-decoration: none;
        font-weight: 700;
        transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
    }

    .btn_main {
        color: var(--white);
        background: var(--text);
        box-shadow: 0 16px 34px rgba(31, 36, 48, .16);
    }

    .btn_plain {
        color: var(--text);
        background: rgba(255, 255, 255, .72);
        border: 1px solid rgba(31, 36, 48, .08);
    }

    .btn_main:hover,
    .btn_plain:hover {
        transform: translateY(-2px);
    }

    .hero_card {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        padding: 24px;
        border: 1px solid rgba(31, 36, 48, .08);
        border-radius: 30px;
        background: rgba(255, 255, 255, .64);
        box-shadow: var(--shadow);
        backdrop-filter: blur(16px);
        transform: rotate(2deg);
    }

    .hero_card span {
        display: inline-flex;
        padding: 10px 14px;
        border-radius: 999px;
        color: var(--text);
        background: var(--white);
        border: 1px solid var(--line);
        font-size: 15px;
        font-weight: 700;
    }

    .hero_card span:nth-child(2n) {
        color: var(--accent-2);
    }

    .hero_card span:nth-child(3n) {
        color: var(--accent);
    }

    .home_section {
        padding: 72px 0;
    }

    .home_section h2 {
        margin: 0;
        color: var(--text);
        font-size: clamp(32px, 4vw, 54px);
        line-height: 1;
        letter-spacing: -.045em;
    }

    .home_section p {
        color: #4a505e;
        font-size: 17px;
        line-height: 1.72;
    }

    .section_kicker {
        display: inline-flex;
        margin-bottom: 14px;
        color: var(--accent);
        font-size: 13px;
        line-height: 1;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .14em;
    }

    .section_big {
        margin-top: 20px;
        color: var(--text) !important;
        font-size: clamp(19px, 2vw, 24px) !important;
        line-height: 1.45 !important;
    }

    .section_grid {
        display: grid;
        grid-template-columns: .85fr 1.15fr;
        gap: 56px;
        align-items: start;
    }

    .section_text {
        padding-top: 34px;
    }

    .events_block {
        background: var(--white);
    }

    .links_panel {
        margin-top: 30px;
    }

    .events_block .links_panel {
        margin-top: 44px;
        padding: 26px;
        border-radius: calc(var(--radius) + 8px);
        background:
                linear-gradient(var(--white), var(--white)) padding-box,
                linear-gradient(135deg, rgba(230, 106, 92, .45), rgba(115, 87, 216, .35)) border-box;
        border: 1px solid transparent;
        box-shadow: var(--shadow);
    }

    .recipients_block {
        background: linear-gradient(180deg, var(--soft), #fff);
    }

    .recipients_layout {
        display: grid;
        grid-template-columns: 340px minmax(0, 1fr);
        gap: 54px;
        align-items: center;
    }

    .recipients_note {
        position: sticky;
        top: 24px;
        padding: 30px;
        border-radius: 34px;
        color: var(--white);
        background: var(--text);
        box-shadow: var(--shadow);
    }

    .recipients_note p {
        margin: 0;
        color: rgba(255, 255, 255, .88);
        font-size: 20px;
        line-height: 1.5;
    }

    .recipients_content {
        padding: 44px;
        border-radius: 36px;
        background: var(--white);
        border: 1px solid var(--line);
    }

    .names_block {
        background: var(--white);
    }

    .names_box {
        position: relative;
        overflow: hidden;
        display: grid;
        grid-template-columns: 240px minmax(0, 1fr);
        gap: 36px;
        padding: 52px;
        border-radius: 40px;
        background:
                radial-gradient(circle at 12% 18%, rgba(230, 106, 92, .13), transparent 28%),
                radial-gradient(circle at 82% 80%, rgba(115, 87, 216, .12), transparent 28%),
                var(--soft);
    }

    .names_decor {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
        align-content: center;
    }

    .names_decor span {
        display: flex;
        aspect-ratio: 1;
        align-items: center;
        justify-content: center;
        border-radius: 24px;
        color: rgba(31, 36, 48, .72);
        background: rgba(255, 255, 255, .76);
        border: 1px solid rgba(31, 36, 48, .06);
        font-size: 42px;
        font-weight: 900;
        box-shadow: 0 12px 30px rgba(31, 36, 48, .05);
    }

    .names_decor span:nth-child(2),
    .names_decor span:nth-child(5) {
        color: var(--accent);
        transform: translateY(18px);
    }

    .names_decor span:nth-child(3) {
        color: var(--accent-2);
    }

    .genres_block {
        color: var(--white);
        background: var(--text);
    }

    .genres_block h2,
    .genres_block .section_big {
        color: var(--white) !important;
    }

    .genres_block p {
        color: rgba(255, 255, 255, .74);
    }

    .genres_header {
        display: grid;
        grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr);
        gap: 48px;
        align-items: end;
        padding-bottom: 34px;
        border-bottom: 1px solid rgba(255, 255, 255, .12);
    }

    .genres_header p {
        margin: 0;
        font-size: 20px;
        line-height: 1.55;
    }


    .genres_block .links_panel {
        margin-top: 0;
        padding: 20px;
    }

    .blog_block {
        background:
                linear-gradient(
                        180deg,
                        rgba(255, 255, 255, 0) 70%,
                        rgba(255, 255, 255, 1) 100%
                ),
                linear-gradient(
                        90deg,
                        rgba(250, 247, 245, .92),
                        rgba(255, 255, 255, 1)
                );
    }

    .blog_layout {
        display: grid;
        grid-template-columns: 420px minmax(0, 1fr);
        gap: 48px;
        align-items: start;
    }

    .blog_intro {
        padding: 34px;
        border-radius: 34px;
        background: var(--white);
        border: 1px solid var(--line);
        box-shadow: var(--shadow);
    }

    .blog_more {
        margin: 26px 0 0;
    }

    .blog_more a {
        display: inline-flex;
        color: var(--accent-2);
        font-weight: 800;
        text-decoration: none;
        border-bottom: 2px solid rgba(115, 87, 216, .25);
    }

    .blog_more a:hover {
        border-color: var(--accent-2);
    }

    .blog_posts_wrap {
        min-width: 0;
    }

    /* Если PHP-блоки выводят обычные ссылки, это их красиво причешет */
    .links_panel a {
        display: inline-flex;
        align-items: center;
        margin: 5px;
        padding: 9px 13px;
        border-radius: 999px;
        color: var(--text);
        background: var(--white);
        border: 1px solid #9d9d9d;
        text-decoration: none;
        font-weight: 350;
        line-height: 1.2;
        transition: transform .18s ease, border-color .18s ease, color .18s ease, background .18s ease;
    }

    .links_panel a:hover {
        transform: translateY(-1px);
        color: var(--accent);
        border-color: rgba(230, 106, 92, .35);
        background: #fffaf8;
    }

    .genres_block .links_panel a {
        background: #f8f6ff;
        border-color: rgba(115, 87, 216, .13);
    }

    .genres_block .links_panel a:hover {
        color: var(--accent-2);
        border-color: rgba(115, 87, 216, .35);
    }

    .links_panel sup {
        background-color: #fff3cd; /* Светло-желтый */
        color: #856404;
        padding: 1px 3px;
        border: 1px solid #ffeeba;
        border-radius: 3px;
        font-size: 0.65em;
        vertical-align: super;
        font-weight: 500 !important;
    }

    @keyframes heroFloat {
        from {
            transform: translate3d(0, 0, 0) rotate(0deg);
        }

        to {
            transform: translate3d(-34px, -24px, 0) rotate(12deg);
        }
    }

    @media (max-width: 991px) {
        .hero_inner,
        .section_grid,
        .recipients_layout,
        .names_box,
        .genres_header,
        .genres_body,
        .blog_layout {
            grid-template-columns: 1fr;
        }

        .hero_block {
            min-height: auto;
            padding: 64px 0 54px;
        }

        .hero_card {
            max-width: 520px;
            transform: none;
        }

        .section_text {
            padding-top: 0;
        }

        .recipients_note {
            position: relative;
            top: auto;
        }

        .names_decor {
            display: flex;
            flex-wrap: wrap;
        }

        .names_decor span {
            width: 74px;
            height: 74px;
            aspect-ratio: auto;
            font-size: 30px;
        }

        .blog_intro {
            padding: 28px;
        }
    }

    @media (max-width: 575px) {
        .hero_block {
            padding: 48px 0 42px;
        }

        .hero_block h1 {
            font-size: 46px;
        }

        .hero_mark {
            font-size: 24px;
        }

        .hero_actions {
            flex-direction: column;
            align-items: stretch;
        }

        .home_section {
            padding: 52px 0;
        }

        .recipients_content,
        .names_box,
        .blog_intro {
            padding: 24px;
            border-radius: 26px;
        }

        .events_block .links_panel,
        .genres_block .links_panel {
            padding: 18px;
            border-radius: 24px;
        }

        .links_panel a {
            margin: 4px;
            padding: 8px 11px;
            font-size: 14px;
        }
    }
    .links_panel_names a.male {
        background-color: #f4f6fa;
    }
    .links_panel_names a.female {
        background-color: #ffecfd;
    }
</style>