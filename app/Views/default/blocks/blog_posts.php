<div class="row g-4">
    <?php foreach ($posts as $post): ?>
        <?php
        $post_url = "/blog/" . $post->alias;
        $img_src = "/images/blogpost/" . $post->id . "/400_" . md5($post->id) . ".webp";
        $img_alt = htmlspecialchars($post->name);

        $word_count = str_word_count(strip_tags($post->text ?? ''));
        $read_time = ceil($word_count / 100); // 100 слов в минуту
        ?>

        <div class="col-12 col-md-6 col-lg-4">
            <article class="blog-card h-100 bg-white" itemscope itemtype="https://schema.org/BlogPosting">
                <a href="<?php echo $post_url; ?>" title="Читать статью: <?php echo htmlspecialchars($post->name); ?>">
                    <img
                        src="<?php echo $img_src; ?>"
                        class="blog-card-img"
                        alt="<?php echo $img_alt; ?>"
                        loading="lazy"
                        width="400"
                        height="200"
                        itemprop="image"
                    >
                </a>
                <div class="blog-card-body">
                    <h2 class="h4 mb-3" itemprop="headline">
                        <a href="<?php echo $post_url; ?>" class="text-dark text-decoration-none" itemprop="url">
                            <?php echo htmlspecialchars($post->name); ?>
                        </a>
                    </h2>

                    <p class="card-text text-muted mb-3" itemprop="description">
                        <?php
                        // Обрезаем описание до разумной длины
                        $desc = strip_tags($post->page_desc ?? '');
                        if(mb_strlen($desc) > 120) {
                            echo mb_substr($desc, 0, 120) . '...';
                        } else {
                            echo $desc;
                        }
                        ?>
                    </p>

                    <div style="display:none;" itemprop="author" itemscope itemtype="https://schema.org/Person">
                        <span itemprop="name">ВСтихах.Ру</span>
                    </div>
                    <div style="display:none;" itemprop="publisher" itemscope itemtype="https://schema.org/Organization">
                        <span itemprop="name">ВСтихах.Ру</span>
                    </div>
                    <?php if(isset($post->add_at)): ?>
                        <meta itemprop="datePublished" content="<?php echo date('Y-m-d', strtotime($post->add_at)); ?>">
                        <meta itemprop="dateModified" content="<?php echo date('Y-m-d', strtotime($post->upd_at ?? $post->add_at)); ?>">
                    <?php endif; ?>
                </div>
                <div class="blog-card-footer">
                    <span class="add-date read-time"><?php echo date('d.m.Y', strtotime($post->add_at)); ?></span>
                    <a href="<?php echo $post_url; ?>" class="btn btn-primary btn-sm" title="Читать статью полностью">
                        Читать статью
                    </a>
                    <span class="read-time">
                        <?php echo $read_time > 0 ? "~{$read_time} мин." : "Быстрое чтение"; ?>
                    </span>
                </div>
            </article>
        </div>
    <?php endforeach; ?>
</div>