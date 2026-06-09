<section>
    <div class="container-lg my-4">
        <h1><?php echo $meta_title; ?></h1>

        <?php if(isset($_SESSION['errors'])): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach($_SESSION['errors'] as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php unset($_SESSION['errors']); ?>
        <?php endif; ?>

        <?php if($published): ?>
            <div class="alert alert-success p-3 text-center">
                Пост <b>"<?php echo htmlspecialchars($published->name); ?>"</b> успешно опубликован!
            </div>
        <?php endif; ?>

        <form method="post" id="add_post_form" enctype="multipart/form-data">

            <div class="mb-3">
                <label for="name" class="form-label">Название поста *</label>
                <input type="text" class="form-control" id="name" name="name" required>
                <div class="form-text">Заполните это поле для автоматической генерации alias</div>
            </div>

            <div class="mb-3">
                <label for="alias" class="form-label">Alias (URL) *</label>
                <input type="text" class="form-control" id="alias" name="alias" required>
                <div class="form-text">Только латинские буквы, цифры и дефисы</div>
            </div>

            <div class="mb-3">
                <label for="meta_title" class="form-label">Meta Title *</label>
                <input type="text" class="form-control" id="meta_title" name="meta_title" required maxlength="250">
            </div>

            <div class="mb-3">
                <label for="meta_desc" class="form-label">Meta Description *</label>
                <textarea class="form-control" id="meta_desc" name="meta_desc" rows="2" required maxlength="500"></textarea>
            </div>

            <div class="mb-3">
                <label for="page_title" class="form-label">Заголовок страницы (H1) *</label>
                <input type="text" class="form-control" id="page_title" name="page_title" required maxlength="250">
            </div>

            <div class="mb-3">
                <label for="page_desc" class="form-label">Описание под заголовком *</label>
                <textarea class="form-control" id="page_desc" name="page_desc" rows="2" required maxlength="500"></textarea>
            </div>

            <div class="mb-3">
                <label for="text" class="form-label">Текст поста *</label>
                <textarea class="form-control" id="text" name="text" rows="10" required></textarea>
            </div>

            <div class="mb-3">
                <label for="img" class="form-label">Изображение *</label>
                <input type="file" class="form-control" id="img" name="img" accept="image/jpeg,image/png,image/webp,image/gif" required>
                <div class="form-text">Допустимые форматы: JPG, PNG, WebP, GIF</div>
            </div>

            <div class="mb-3">
                <button type="submit" class="btn btn-primary">Опубликовать пост</button>
            </div>
        </form>
    </div>
</section>

<script>
    // Функция транслитерации русского текста в латиницу
    function transliterate(text) {
        const translitMap = {
            'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd',
            'е': 'e', 'ё': 'yo', 'ж': 'zh', 'з': 'z', 'и': 'i',
            'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n',
            'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't',
            'у': 'u', 'ф': 'f', 'х': 'kh', 'ц': 'ts', 'ч': 'ch',
            'ш': 'sh', 'щ': 'shch', 'ъ': '', 'ы': 'y', 'ь': '',
            'э': 'e', 'ю': 'yu', 'я': 'ya',
            ' ': '-', '_': '-', ',': '', '.': '', '!': '', '?': '',
            '(': '', ')': '', '[': '', ']': '', '{': '', '}': '',
            '@': '', '#': '', '$': '', '%': '', '^': '', '&': '',
            '*': '', '+': '', '=': '', ';': '', ':': '', '"': '',
            "'": '', '<': '', '>': '', '/': '', '\\': '', '|': ''
        };

        let result = '';
        text = text.toLowerCase();

        for(let i = 0; i < text.length; i++) {
            const char = text[i];
            if(translitMap[char] !== undefined) {
                result += translitMap[char];
            } else if(/[a-z0-9-]/.test(char)) {
                result += char;
            }
        }

        // Убираем двойные дефисы и дефисы в начале/конце
        result = result.replace(/--+/g, '-').replace(/^-|-$/g, '');

        return result;
    }

    // Генерация alias при вводе в поле name
    document.getElementById('name').addEventListener('input', function(e) {
        const aliasField = document.getElementById('alias');

        // Если alias еще не редактировался вручную или пустой
        if(!aliasField.dataset.manualEdit) {
            const transliterated = transliterate(e.target.value);
            aliasField.value = transliterated;
        }
    });

    // Помечаем поле alias как отредактированное вручную
    document.getElementById('alias').addEventListener('input', function(e) {
        this.dataset.manualEdit = 'true';
    });

    // Валидация формы перед отправкой
    document.getElementById('add_post_form').addEventListener('submit', function(e) {
        const aliasField = document.getElementById('alias');
        const aliasValue = aliasField.value.trim();

        // Проверка формата alias
        if(!/^[a-z0-9-]+$/.test(aliasValue)) {
            alert('Alias может содержать только латинские буквы в нижнем регистре, цифры и дефисы');
            aliasField.focus();
            e.preventDefault();
            return false;
        }

        // Проверка расширения файла
        const fileInput = document.getElementById('img');
        if(fileInput.files.length > 0) {
            const fileName = fileInput.files[0].name.toLowerCase();
            const allowedExtensions = /(\.jpg|\.jpeg|\.png|\.webp|\.gif)$/i;

            if(!allowedExtensions.exec(fileName)) {
                alert('Недопустимый формат файла. Разрешены только JPG, PNG, WebP, GIF');
                e.preventDefault();
                return false;
            }
        }

        return true;
    });
</script>