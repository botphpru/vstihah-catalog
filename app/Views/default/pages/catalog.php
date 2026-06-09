<section class="catalog_page">
    <div class="container">
        <div class="col-12 col-md-8 mx-auto">
            <h1><?= htmlspecialchars($page_title) ?></h1>
            <?php if (!empty($filter_array)): ?>
            <h2>Фильтр</h2>
                <?php
                $arr_keys = [
                        'event' => 'Праздник',
                        'recipient' => 'Кому',
                        'name' => 'Имя',
                        'genre' => 'Стиль'
                ];

                foreach ($filter_array as $key => $filter) {
                    if (empty($filter)) continue;

                    echo '<h3 class="h6 mt-3 mb-2 ">'.$arr_keys[$key].'</h3>';
                    echo '<div class="d-flex flex-wrap gap-1">';

                    foreach ($filter as $item) {
                        // Класс кнопки: активный или обычный
                        $class = $item['is_active'] ? 'btn-secondary' : 'btn-outline-secondary';

                        // Контент: для активного показываем "×", для обычного — количество
                        $badge = $item['is_active']
                                ? '<span class="badge bg-danger rounded-pill">×</span>'
                                : '<span class="badge bg-light text-dark rounded-pill">'.$item['count'].'</span>';

                        echo '<a href="'.$item['href'].'" class="btn btn-sm '.$class.'">'.
                                htmlspecialchars($item['name']).$badge.
                                '</a>';
                    }

                    echo '</div>';
                }
                ?>
            <?php endif; ?>

            <?php if ($poems):?>

            <?php if(count($poems) > 1):?>
            <div class="row align-content-center my-3">
                <div class="col-12 col-md-auto total-count align-self-center">Найдено <span class="fw-bolder"><?php echo $total_count;?></span> <?php echo pluralize($total_count, 'стихотворение', 'стихотворения', 'стихотворений');?>.</div>
                <div class="col-12 col-md-auto sort-wrap">
                    <span class="sort-label">Сортировать: </span>
                    <select class="form-select form-select-sm" id="poems_sort">
                        <? foreach ($sort_names as $value => $sort_name) {
                            $selected = $value == $current_sort_value ? ' selected' : '';
                            echo '<option value="'.$value.'"'.$selected.'>'.$sort_name.'</option>';
                        }?>
                    </select>
                </div>
            </div>
            <? endif;?>
            <div class="poems-wrap" id="poems_wrap">
                <?php foreach ($poems as $poem): ?>
                    <article class="poem_text">
                        <p><?= nl2br(htmlspecialchars($poem->text)) ?></p>
                        <div class="poem-footer">
                            <div class="poem-rating">
                                <button type="button" data-rating-id="minus_<?= $poem->id ?>">👎</button>
                                <span class="rating-value"><?= $poem->rating ?></span>
                                <button type="button" data-rating-id="plus_<?= $poem->id ?>">👍</button>
                            </div>
                            <div class="poem-date">Опубликовано: <?= $poem->getRussianDate() ?></div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
                <p>Пока нет стихов с такими параметрами. <a href="/">Вернуться на главную</a></p>
            <?php endif; ?>

            <?php if(!empty($pagination_html)): ?>
            <div class="pagination-wrap text-center py-2 my-2">
                <? echo $pagination_html;?>
            </div>
            <?php endif; ?>
            <div class="bottom_text">
                <p class="mb-0"><? echo $bottom_text;?></p>
            </div>

            <? if(isset($blog_posts_block)):?>
            <div class="blog_home my-4">
                <h2 class="h4 text-center mb-3 fw-bold">Последние записи в блоге</h2>
                <?php echo $blog_posts_block;?>
            </div>
            <? endif;?>
        </div>
    </div>
</section>