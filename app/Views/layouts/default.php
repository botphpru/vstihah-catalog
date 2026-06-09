<!DOCTYPE html>
<html lang="ru" prefix="og: http://ogp.me/ns# article: http://ogp.me/ns/article#">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $meta_title ?? ''; ?></title>
    <?php if (isset($meta_desc)): ?>
        <meta name="description" content="<?php echo htmlspecialchars($meta_desc); ?>">
    <?php endif; ?>
    <?php if (isset($canonical)) echo '<link rel="canonical" href="' . htmlspecialchars($canonical, ENT_QUOTES) . '">'; ?>
    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="MyWebSite" />
    <link rel="manifest" href="/site.webmanifest" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Huninn&family=Neucha&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32)); ?>">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <?php if (isset($dop_head)) echo $dop_head; ?>
</head>
<body>
<?php if(request()->getFirstDir() != '') require_once __DIR__.'/../default/blocks/header.php'; ?>
<?php echo $breadcrumbs_html ?? ''; ?>
<main>
    <?php echo $content ?? ''; ?>
</main>
<?php include_once __DIR__.'/../default/blocks/footer.php'; ?>
<?php echo $breadcrumbs_json ?? ''; ?>
<div id="cookieConsentBanner" class="fixed-bottom p-3 bg-dark text-light d-none shadow" style="z-index: 1050;">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
        <div class="text-center text-md-start">
            Мы используем файлы cookie для улучшения работы сайта. Продолжая использовать сайт, вы соглашаетесь с нашей
            <a href="https://<?php echo HOME_DOMAIN;?>/privacy" class="text-info text-decoration-underline">Политикой конфиденциальности</a>.
        </div>
        <button id="acceptCookiesBtn" class="btn btn-primary text-nowrap px-4">Принять</button>
    </div>
</div>
<script src="/assets/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/main.js"></script>
<script type="text/javascript">
    (function(m,e,t,r,i,k,a){
        m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
        m[i].l=1*new Date();
        for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
        k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
    })(window, document,'script','https://mc.yandex.ru/metrika/tag.js?id=109236095', 'ym');

    ym(109236095, 'init', {ssr:true, clickmap:true, ecommerce:"dataLayer", referrer: document.referrer, url: location.href, accurateTrackBounce:true, trackLinks:true});
</script><noscript><div><img src="https://mc.yandex.ru/watch/109236095" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
</body>
</html>