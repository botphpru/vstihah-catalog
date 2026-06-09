<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация</title>

    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-light">

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100 mb-3">
        <div class="col-md-6 col-lg-5 col-xl-4">

            <div class="text-center mb-4">
                <div class="mb-3">
                    <i class="bi bi-shield-lock-fill text-primary" style="font-size: 3rem;"></i>
                </div>
                <h1 class="h3 fw-normal">Авторизация</h1>
                <p class="text-muted">Введите ваши учетные данные для входа</p>
                <? if(isset($error)):?>
                <p class="text-danger"><? echo $error;?></p>
                <? endif;?>

            </div>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">
                                email
                            </label>
                            <div class="input-group">
                                <input type="email"
                                       class="form-control"
                                       id="email"
                                       name="email"
                                       placeholder="email"
                                       required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                Пароль
                            </label>
                            <div class="input-group">
                                <input type="password"
                                       class="form-control"
                                       id="password"
                                       name="password"
                                       placeholder="Введите пароль"
                                       required>
                                <button class="btn btn-outline-secondary"
                                        type="button"
                                        id="togglePassword">
                                    👁
                                </button>
                            </div>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Войти
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // JavaScript для переключения видимости пароля
    document.addEventListener('DOMContentLoaded', function() {
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

            });
        }
    });
</script>
<script src="/assets/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/main.js"></script>
</body>
</html>