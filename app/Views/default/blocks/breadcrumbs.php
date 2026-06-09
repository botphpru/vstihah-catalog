<section class="container my-3">
    <div class="col-12 col-md-8 mx-auto">
    <nav aria-label="breadcrumb">
        <ul itemscope itemtype="https://schema.org/BreadcrumbList" class="breadcrumb mb-0">
            <?
            $i = 1;
            foreach ($breadcrumbsArr as $key => $value): ?>
                <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" class="breadcrumb-item">
                    <?
                    if ($key === array_key_last($breadcrumbsArr)) { ?>

                        <a title="<? echo $key;?>" itemprop="item">
                            <span itemprop="name"><? echo $key;?></span>
                            <meta itemprop="position" content="<? echo $i;?>">
                        </a>

                    <? } else { ?>

                        <a href="<? echo $value;?>" title="<? echo $key;?>" itemprop="item">
                            <span itemprop="name"><? echo $key;?></span>
                            <meta itemprop="position" content="<? echo $i;?>">
                        </a>

                    <? } ?>
                </li>
                <?
                $i++;
            endforeach;?>
        </ul>
    </nav>
    </div>
</section>