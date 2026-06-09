<section>
    <div class="container-lg mb-3">
    <h1><? echo $type;?> - <? echo $date;?></h1>
        <div class="mb-3">
            <a href="<?= $this->url('/admin123') ?>" class="btn btn-secondary">← Назад</a>
            <a href="<?= $this->url('/admin123/logs/' . $type . '/' . $date . '.txt/download') ?>"
               class="btn btn-primary">Скачать</a>
        </div>
    <textarea style="width: 100%; height: 800px; padding: 8px;">
        <? echo $content;?>
    </textarea>
    </div>
</section>