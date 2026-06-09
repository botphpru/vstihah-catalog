<footer class="site-footer">
    <div class="site-footer__inner">
        <div class="site-footer__grid">
            <div>
                <a class="site-logo" href="/" aria-label="ВСтихах.Ру">
                    <img src="/images/logo.svg" class="site-logo__mark" alt="Встихах.ру - стихи поздравления" />
                    <span>В<span class="site-logo__accent">Стихах</span>.Ру</span>
                </a>

                <p class="site-footer__brand-text">
                    ВСтихах.Ру — каталог поздравлений в стихах для праздников, родных, друзей, коллег и особых дат.
                    Здесь легко найти подходящие строки по получателю, имени, стилю или поводу.
                </p>

                <a class="telegram-link" href="https://t.me/vstihah_ru" target="_blank" rel="noopener">
                    <img src="/images/tg.svg" width="15" height="15"> Блог
                </a>

                <a class="telegram-link" href="https://t.me/pozdravleniya_stihi" target="_blank" rel="noopener">
                    <img src="/images/tg.svg" width="15" height="15"> Стихи
                </a>
            </div>

            <div>
                <div class="site-footer__title">Разделы сайта</div>

                <ul class="site-footer__links">
                    <li><a href="/about">О сайте</a></li>
                    <li><a href="/privacy">Политика конфиденциальности</a></li>
                    <li><a href="/contacts">Контакты</a></li>
                    <li><a href="/blog">Блог</a></li>
                </ul>
            </div>

            <div>
                <div class="site-footer__title">Статистика каталога поздравлений</div>

                <div class="site-footer__stats">
                    <div class="footer-stat">
                        <span class="footer-stat__value">
                            <?php
                            $stat_1 = $total_count_poems ?? 10000;
                            echo ($stat_1 < 1000 ? '1' : number_format($stat_1 / 1000, 1, ',', '')) . 'k';
                            ?>
                        </span>
                        <span class="footer-stat__label">поздравлений</span>
                    </div>

                    <div class="footer-stat">
                        <span class="footer-stat__value"><?php
                        echo $total_count_genres ?? 9;
                        ?></span>
                        <span class="footer-stat__label">стилей</span>
                    </div>

                    <div class="footer-stat">
                        <span class="footer-stat__value"><?php
                            echo $total_count_events ?? 39;
                            ?></span>
                        <span class="footer-stat__label">видов праздников</span>
                    </div>

                    <div class="footer-stat">
                        <span class="footer-stat__value"><?php
                            $stat_2 = $total_count_male_names ?? 25;
                            $stat_3 = $total_count_female_names ?? 25;
                            echo $stat_2 + $stat_3;
                        ?></span>
                        <span class="footer-stat__label">имён: <?php echo $stat_2;?> мужских и <?php echo $stat_3;?> женских</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="site-footer__bottom">
            <div>© <?php echo date('Y'); ?> <a href="https://<?php echo HOME_DOMAIN;?>" title="Поздравления в стихах">ВСтихах.Ру</a></div>
            <div>Поздравления в стихах для тёплых слов и важных моментов.</div>
        </div>
    </div>
</footer>