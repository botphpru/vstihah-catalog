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

<header class="border-bottom">
    <div class="container-lg">
        <div class="d-flex justify-content-center py-3">
            <ul class="nav nav-pills">
                <li class="nav-item"><a href="/" class="nav-link">Главная</a></li>
                <li class="nav-item"><a href="/admin123" class="nav-link">Логи</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">Блог</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/admin123/blog/posts/add">Добавить</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</header>

<main>
    <?php echo $content ?? '';?>
</main>

<footer class="footer bg-dark text-white mt-auto">
    <div class="container-lg">
        <div class="text-center text-white py-2"><? echo date('Y');?> MRTPHP</div>
    </div>
</footer>
<script src="/assets/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/main.js"></script>
</body>
</html>