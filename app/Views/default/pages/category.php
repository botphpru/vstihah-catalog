<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Категория</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .category-header {
            background-color: #f8f9fa;
            padding: 60px 0;
        }
        .card {
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .filter-btn.active {
            background-color: #0d6efd;
            color: white;
        }
    </style>
</head>
<body>
<!-- Category Header -->
<section class="category-header">
    <div class="container-lg">
        <div class="row">
            <div class="col-md-8">
                <h1 class="display-4 fw-bold">Название категории</h1>
                <p class="lead">Описание категории. Здесь вы можете рассказать о том, какие записи представлены в этой категории.</p>
            </div>
            <div class="col-md-4">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" placeholder="Поиск по категории...">
                    <button class="btn btn-primary" type="button">Найти</button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Filters Section -->
<section class="py-4 bg-light">
    <div class="container-lg">
        <div class="row align-items-center">
            <div class="col-md-6">
                <span class="me-3">Фильтровать по:</span>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary filter-btn active">Все</button>
                    <button type="button" class="btn btn-outline-primary filter-btn">Популярные</button>
                    <button type="button" class="btn btn-outline-primary filter-btn">Новые</button>
                    <button type="button" class="btn btn-outline-primary filter-btn">Старые</button>
                </div>
            </div>
            <div class="col-md-6 text-md-end">
                <span class="me-2">Сортировать по:</span>
                <select class="form-select d-inline-block w-auto">
                    <option selected>Дате публикации</option>
                    <option>Названию</option>
                    <option>Популярности</option>
                </select>
            </div>
        </div>
    </div>
</section>

<!-- Posts Grid -->
<section class="py-5">
    <div class="container-lg">
        <div class="row g-4">
            <!-- Post Card 1 -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <img src="https://via.placeholder.com/400x250" class="card-img-top" alt="Запись 1">
                    <div class="card-body d-flex flex-column">
                        <div class="mb-2">
                            <span class="badge bg-primary">Категория</span>
                            <span class="text-muted small ms-2">12 мая 2023</span>
                        </div>
                        <h5 class="card-title">Заголовок записи 1</h5>
                        <p class="card-text flex-grow-1">Краткое описание записи. Здесь можно разместить аннотацию к статье или посту, которая заинтересует читателя.</p>
                        <div class="mt-auto">
                            <a href="#" class="btn btn-primary">Читать далее</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Post Card 2 -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <img src="https://via.placeholder.com/400x250" class="card-img-top" alt="Запись 2">
                    <div class="card-body d-flex flex-column">
                        <div class="mb-2">
                            <span class="badge bg-success">Категория</span>
                            <span class="text-muted small ms-2">10 мая 2023</span>
                        </div>
                        <h5 class="card-title">Заголовок записи 2</h5>
                        <p class="card-text flex-grow-1">Краткое описание записи. Здесь можно разместить аннотацию к статье или посту, которая заинтересует читателя.</p>
                        <div class="mt-auto">
                            <a href="#" class="btn btn-primary">Читать далее</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Post Card 3 -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <img src="https://via.placeholder.com/400x250" class="card-img-top" alt="Запись 3">
                    <div class="card-body d-flex flex-column">
                        <div class="mb-2">
                            <span class="badge bg-warning text-dark">Категория</span>
                            <span class="text-muted small ms-2">8 мая 2023</span>
                        </div>
                        <h5 class="card-title">Заголовок записи 3</h5>
                        <p class="card-text flex-grow-1">Краткое описание записи. Здесь можно разместить аннотацию к статье или посту, которая заинтересует читателя.</p>
                        <div class="mt-auto">
                            <a href="#" class="btn btn-primary">Читать далее</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Post Card 4 -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <img src="https://via.placeholder.com/400x250" class="card-img-top" alt="Запись 4">
                    <div class="card-body d-flex flex-column">
                        <div class="mb-2">
                            <span class="badge bg-danger">Категория</span>
                            <span class="text-muted small ms-2">5 мая 2023</span>
                        </div>
                        <h5 class="card-title">Заголовок записи 4</h5>
                        <p class="card-text flex-grow-1">Краткое описание записи. Здесь можно разместить аннотацию к статье или посту, которая заинтересует читателя.</p>
                        <div class="mt-auto">
                            <a href="#" class="btn btn-primary">Читать далее</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Post Card 5 -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <img src="https://via.placeholder.com/400x250" class="card-img-top" alt="Запись 5">
                    <div class="card-body d-flex flex-column">
                        <div class="mb-2">
                            <span class="badge bg-info">Категория</span>
                            <span class="text-muted small ms-2">3 мая 2023</span>
                        </div>
                        <h5 class="card-title">Заголовок записи 5</h5>
                        <p class="card-text flex-grow-1">Краткое описание записи. Здесь можно разместить аннотацию к статье или посту, которая заинтересует читателя.</p>
                        <div class="mt-auto">
                            <a href="#" class="btn btn-primary">Читать далее</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Post Card 6 -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <img src="https://via.placeholder.com/400x250" class="card-img-top" alt="Запись 6">
                    <div class="card-body d-flex flex-column">
                        <div class="mb-2">
                            <span class="badge bg-secondary">Категория</span>
                            <span class="text-muted small ms-2">1 мая 2023</span>
                        </div>
                        <h5 class="card-title">Заголовок записи 6</h5>
                        <p class="card-text flex-grow-1">Краткое описание записи. Здесь можно разместить аннотацию к статье или посту, которая заинтересует читателя.</p>
                        <div class="mt-auto">
                            <a href="#" class="btn btn-primary">Читать далее</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Pagination -->
<section class="py-4">
    <div class="container-lg">
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1">Предыдущая</a>
                </li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item">
                    <a class="page-link" href="#">Следующая</a>
                </li>
            </ul>
        </nav>
    </div>
</section>

<!-- Newsletter Section -->
<section class="py-5 bg-light">
    <div class="container-lg">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <h2 class="display-6">Подпишитесь на обновления</h2>
                <p class="lead mb-4">Получайте уведомления о новых записях в этой категории</p>
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Ваш email">
                            <button class="btn btn-primary" type="button">Подписаться</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Активация кнопок фильтра
    document.addEventListener('DOMContentLoaded', function() {
        const filterBtns = document.querySelectorAll('.filter-btn');

        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                filterBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });
    });
</script>
</body>
</html>