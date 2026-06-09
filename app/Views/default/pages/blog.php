<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Blog",
        "name": "Блог о стихах и поздравлениях",
        "description": "Новости, полезные статьи, инструкции, советы про поздравления в стихах",
        "publisher": {
            "@type": "Organization",
            "name": "ВСтихах.Ру",
            "url": "https://vstihah.ru"
        },
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "https://vstihah.ru/blog"
        }
    }
</script>
<style>
    /* =========================================
   Blog Home - Minimal Card Design
   ========================================= */


</style>

<section class="blog_home">
    <div class="container">
        <div class="col-12 col-md-8 mx-auto">
            <header class="mb-5 text-center">
                <h1 class="display-5 fw-bold mb-3"><?php echo $page_title;?></h1>
                <p class="lead text-muted max-w-800 mx-auto"><?php echo $page_desc;?></p>
            </header>

            <?php echo $blog_posts_block; ?>

            <?php if($pagination_html):?>
                <div class="pagination-wrap text-center py-2 my-2">
                    <? echo $pagination_html;?>
                </div>
            <? endif;?>
        </div>
    </div>
</section>