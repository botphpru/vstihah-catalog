<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $meta_title ?? ''; ?></title>
    <? if(isset($meta_desc)): ?>
        <meta name="description" content="<? echo htmlspecialchars($meta_desc);?>">
    <? endif; ?>
    <? if(isset($canonical)) echo '<link rel="canonical" href="'.$canonical.'">';?>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <? if(isset($dop_head)) echo $dop_head;?>
</head>
<body class="d-flex flex-column min-vh-100">

<main>
    <?php echo $content ?? '';?>
</main>

<footer class="footer bg-dark text-white mt-auto">
    <div class="container-lg">
        <div class="text-center text-white py-2"><? echo date('Y');?> <?php echo HOME_DOMAIN;?></div>
    </div>
</footer>
<script src="/assets/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/main.js"></script>
</body>
</html>