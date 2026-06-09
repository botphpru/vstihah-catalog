<?php
$int_and_zero = '[0-9]*';
$int = '[1-9][0-9]*'; // положительное число от 1
$normStr = '[0-9a-z-_]+'; //цифры, анг нижрегистр буквы, тире, подчеркивание
$normStrCS = '[0-9a-zA-Z-_]+'; //цифры, анг нижрегистр буквы, тире, подчеркивание
$date ='[0-9-]+';
$str = '[0-9a-z-]+'; //цифры, анг нижрегистр буквы

app()->router->get('/', [\App\Controllers\HomeController::class, 'index']);
app()->router->get('/about', [\App\Controllers\PageController::class, 'about']);
app()->router->get('/contacts', [\App\Controllers\PageController::class, 'contacts']);
app()->router->get('/privacy', [\App\Controllers\PageController::class, 'privacy']);

//images
app()->router->get('/images/(blogpost)/('.$int.')/('.$str.')_('.$str.')\.webp', [\App\Controllers\ImageController::class, 'getImage']);
app()->router->get('/images/catalog/('.$int_and_zero.')/('.$str.')_('.$str.')\.webp', [\App\Controllers\ImageController::class, 'getCatalogImage']);

//blog
app()->router->get('/blog/('.$normStr.')', [\App\Controllers\BlogController::class, 'getBlogPost']);
app()->router->get('/blog', [\App\Controllers\BlogController::class, 'getBlog']);

//api
app()->router->post('/api/poems/update_rating', [\App\Controllers\ApiController::class, 'poemRating']);

app()->router->get('/auth/login', [\App\Controllers\AuthController::class, 'login']);
app()->router->post('/auth/login', [\App\Controllers\AuthController::class, 'loginForm']);
app()->router->get('/auth/logout', [\App\Controllers\AuthController::class, 'logout']);
app()->router->get('/admin123', [\App\Controllers\AdminPanelController::class, 'index']);
app()->router->get('/admin123/logs/('.$normStrCS.')/('.$date.').txt', [\App\Controllers\LogsController::class, 'show']);
app()->router->get('/admin123/logs/('.$normStrCS.')/('.$date.').txt/download', [\App\Controllers\LogsController::class, 'download']);
app()->router->get('/admin123/blog/posts/add', [\App\Controllers\AdminBlogController::class, 'addPost']);
app()->router->post('/admin123/blog/posts/add', [\App\Controllers\AdminBlogController::class, 'addPost']);

app()->router->get('/('.$normStr.')', [\App\Controllers\CatalogController::class, 'catalogPage']);
app()->router->get('/('.$normStr.')/('.$normStr.')', [\App\Controllers\CatalogController::class, 'catalogPage']);
app()->router->get('/('.$normStr.')/('.$normStr.')/('.$normStr.')', [\App\Controllers\CatalogController::class, 'catalogPage']);
app()->router->get('/('.$normStr.')/('.$normStr.')/('.$normStr.')/('.$normStr.')', [\App\Controllers\CatalogController::class, 'catalogPage']);