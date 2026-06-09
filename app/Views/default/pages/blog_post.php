<section class="blog_post">
    <div class="container">
        <div class="col-12 col-md-8 mx-auto">
            <article class="blogpost" itemscope itemtype="https://schema.org/BlogPosting">
                <h1 itemprop="headline"><?php echo htmlspecialchars($page_title); ?></h1>
                <p class="lead" itemprop="description"><?php echo htmlspecialchars($page_desc); ?></p>
                <div class="post_featured_image">
                    <img
                            src="https://<?php echo HOME_DOMAIN; ?>/images/blogpost/<?php echo $post->id; ?>/1200_<?php echo md5($post->id); ?>.webp"
                            srcset="/images/blogpost/<?php echo $post->id; ?>/640_<?php echo md5($post->id); ?>.webp 640w, /images/blogpost/<?php echo $post->id; ?>/1200_<?php echo md5($post->id); ?>.webp 1200w"
                            sizes="(max-width: 640px) 640px, (max-width: 1200px) 1200px, 1200px"
                            alt="<?php echo htmlspecialchars($page_title); ?> - статья о мессенджере MAX"
                            loading="lazy"
                            class="img-fluid rounded"
                            itemprop="image"
                    >
                </div>
                <div class="post_body" itemprop="articleBody">
                    <?php echo $text; ?>
                </div>
                <div class="post_meta">
                    <div class="meta_item">
                        <i>👤</i>
                        <span>Автор: <a href="/about" itemprop="author" itemscope itemtype="https://schema.org/Person"><span itemprop="name">Семён Авдосов</span></a></span>
                    </div>
                    <div class="meta_item">
                        <i>📅</i>
                        <span>Опубликовано: <time datetime="<?php echo date('Y-m-d', strtotime($post->add_at)); ?>" itemprop="datePublished"><?php echo date('d.m.Y', strtotime($post->add_at)); ?></time></span>
                    </div>
                    <?php if($post->public_upd_at): ?>
                        <div class="meta_item">
                            <i>🔄</i>
                            <span>Обновлено: <time datetime="<?php echo date('Y-m-d', strtotime($post->public_upd_at)); ?>" itemprop="dateModified"><?php echo date('d.m.Y', strtotime($post->public_upd_at)); ?></time></span>
                        </div>
                    <?php endif; ?>
                    <div class="meta_item">
                        <i>👁</i>
                        <span>Прочитали: <?php echo $view_count . ' ' . pluralize($view_count, 'раз', 'раза', 'раз'); ?>.</span>
                    </div>
                </div>
                <div class="author_box" itemprop="author" itemscope itemtype="https://schema.org/Person">
                    <img src="/images/author-1.jpg" alt="Семён Авдосов - автор статьи" class="author_avatar" itemprop="image">
                    <div>
                        <h3 itemprop="name">Семён Авдосов</h3>
                        <p class="author_bio" itemprop="description">
                            Меня зовут Семён, я влюблён в поэзию и уже десять лет пытаюсь поймать идеальную рифму в своих стихах. Верю, что слова способны соединять людей, поэтому рад делиться своими творческими поисками здесь. Надеюсь, мои строки найдут отклик в вашем сердце.
                        </p>
                    </div>
                </div>

                <div class="d-flex gap-2 mb-4">
                    <span class="text-muted me-2">Поделиться:</span>
                    <a href="https://vk.com/share.php?url=https://<?php echo HOME_DOMAIN . $_SERVER['REQUEST_URI']; ?>&title=<?php echo urlencode($page_title); ?>"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="btn btn-sm btn-outline-primary">
                        VK
                    </a>
                    <a href="https://t.me/share/url?url=https://<?php echo HOME_DOMAIN . $_SERVER['REQUEST_URI']; ?>&text=<?php echo urlencode($page_title); ?>"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="btn btn-sm btn-outline-primary">
                        Telegram
                    </a>
                </div>
            </article>
            <div class="nav-posts-wrap">
                <?php if(isset($prev_post) && $prev_post):
                    $img_src = "/images/blogpost/" . $prev_post->id . "/400_" . md5($prev_post->id) . ".webp";
                    ?>
                    <a href="/blog/<?php echo $prev_post->alias; ?>"
                       class="nav-post prev"
                       style="--nav-thumb: url('<?php echo htmlspecialchars($img_src); ?>')">
                        <span class="nav-text">← <?php echo htmlspecialchars(mb_substr($prev_post->name, 0, 40)) . (mb_strlen($prev_post->name) > 40 ? '...' : ''); ?> </span>
                    </a>
                <?php endif; ?>

                <a href="/blog" class="nav-all">Все статьи</a>

                <?php if(isset($next_post) && $next_post):
                    $img_src = "/images/blogpost/" . $next_post->id . "/400_" . md5($next_post->id) . ".webp";
                    ?>
                    <a href="/blog/<?php echo $next_post->alias; ?>"
                       class="nav-post next"
                       style="--nav-thumb: url('<?php echo htmlspecialchars($img_src); ?>')">
                        <span class="nav-text"><?php echo htmlspecialchars(mb_substr($next_post->name, 0, 40)) . (mb_strlen($next_post->name) > 40 ? '...' : ''); ?> → </span>
                    </a>
                <?php endif; ?>
            </div>
            <?php /* include VIEWS.'/default/blocks/comments.php'; */?>
        </div>
    </div>
</section>