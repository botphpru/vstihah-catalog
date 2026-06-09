<?php

namespace App\Controllers;
/**
 * Контроллер для управления блогом в админ-панели.
 *
 * Обрабатывает создание постов: валидация формы, загрузка изображений,
 * сохранение в БД, обработка ошибок и рендеринг шаблонов.
 *
 * @package App\Controllers
 * @extends AdminController
 */
class AdminBlogController extends AdminController
{
    /**
     * Обработчик страницы добавления поста в блог.
     *
     * При GET-запросе отображает форму добавления поста.
     * При POST-запросе:
     * - Валидирует обязательные поля и загруженный файл
     * - Проверяет формат alias (латиница, цифры, дефисы)
     * - Загружает изображение через handleImageUpload()
     * - Сохраняет данные в БД через BlogPost::insertByArr()
     * - При ошибках записывает их в $_SESSION['errors']
     * - При успехе передаёт опубликованный пост в шаблон
     *
     * Побочные эффекты:
     * - Может создавать файлы в /images_original/
     * - Модифицирует $_SESSION['errors'] при ошибках
     * - Выполняет запросы к БД
     *
     * @return void Результат рендеринга шаблона 'pages/add_post'
     */
    public function addPost() {
        $published = null;

        if($_POST) {
            // Валидация обязательных полей
            $requiredFields = [
                'alias', 'name', 'meta_title', 'meta_desc',
                'page_title', 'page_desc', 'text'
            ];

            $errors = [];

            foreach($requiredFields as $field) {
                if(empty($_POST[$field]) && $_POST[$field] !== '0') {
                    $errors[] = "Поле " . $this->getFieldName($field) . " обязательно для заполнения";
                }
            }

            // Проверка загрузки файла
            if(empty($_FILES['img']['name'])) {
                $errors[] = "Изображение обязательно для загрузки";
            } elseif($_FILES['img']['error'] !== UPLOAD_ERR_OK) {
                $errors[] = "Ошибка при загрузке файла: " . $this->getUploadError($_FILES['img']['error']);
            }

            // Проверка формата alias
            if(!preg_match('/^[a-z0-9-]+$/', $_POST['alias'])) {
                $errors[] = "Alias может содержать только латинские буквы в нижнем регистре, цифры и дефисы";
            }

            if(empty($errors)) {
                // Обработка загрузки изображения
                $uploadResult = $this->handleImageUpload($_POST['alias'], $_FILES['img']);

                if($uploadResult['success']) {
                    $text = trim(str_replace('—', '-', $_POST['text']));
                    $arr_insert = [
                        'alias' => $_POST['alias'],
                        'name' => trim($_POST['name']),
                        'meta_title' => trim($_POST['meta_title']),
                        'meta_desc' => trim($_POST['meta_desc']),
                        'page_title' => trim($_POST['page_title']),
                        'page_desc' => trim($_POST['page_desc']),
                        'text' => $text,
                        'img' => $uploadResult['path']
                    ];
                    try {
                        \App\Models\BlogPost::insertByArr($arr_insert);
                        $published = \App\Models\BlogPost::findByAlias($_POST['alias']);

                    } catch(\Exception $e) {
                        // Удаляем загруженный файл при ошибке в базе
                        if(file_exists($uploadResult['full_path'])) {
                            unlink($uploadResult['full_path']);
                        }
                        $errors[] = "Ошибка при сохранении в базу данных: " . $e->getMessage();
                        $_SESSION['errors'] = $errors;
                    }
                } else {
                    $errors[] = $uploadResult['error'];
                    $_SESSION['errors'] = $errors;
                }
            } else {
                $_SESSION['errors'] = $errors;
            }
        }

        $this->render('pages/add_post', [
            'meta_title' => 'Добавление поста в блог',
            'published' => $published
        ]);
    }

    /**
     * Загружает и валидирует изображение для поста блога.
     *
     * Выполняет комплексную проверку:
     * - Расширение файла (jpg, jpeg, png, webp, gif)
     * - MIME-тип через finfo (защита от подмены расширения)
     * - Размер файла (макс. 10 MB)
     * - Создаёт директорию вида /images_original/{first_char_of_md5}/{md5_alias}.{ext}
     * - Устанавливает права 0644 на сохранённый файл
     *
     * @param string $alias Уникальный алиас поста (используется для генерации пути)
     * @param array $file Данные файла из $_FILES['img'] с ключами: name, tmp_name, error, size
     *
     * @return array {
     *     @type bool   $success    true если загрузка успешна
     *     @type string $path       Путь относительно WWW для сохранения в БД (например, '/images_original/a/abc123.webp')
     *     @type string $full_path  Абсолютный путь к файлу на сервере
     *     @type string $error      Текст ошибки если $success === false
     * }
     */
    private function handleImageUpload($alias, $file) {
        $result = ['success' => false, 'path' => '', 'full_path' => '', 'error' => ''];

        // Определяем расширение файла
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if(!in_array($fileExtension, $allowedExtensions)) {
            $result['error'] = "Недопустимый формат файла. Разрешены: " . implode(', ', $allowedExtensions);
            return $result;
        }

        // Проверка типа файла по MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif'
        ];

        if(!isset($allowedMimes[$mime]) || $allowedMimes[$mime] !== $fileExtension) {
            $result['error'] = "Несоответствие расширения файла и его содержимого";
            return $result;
        }

        // Проверка размера файла (например, максимум 5MB)
        $maxSize = 10 * 1024 * 1024;
        if($file['size'] > $maxSize) {
            $result['error'] = "Файл слишком большой. Максимальный размер: 10MB";
            return $result;
        }

        // Генерируем пути
        $base_img_path = '/images_original/' . substr(md5($alias), 0, 1) . '/' . md5($alias) . '.' . $fileExtension;
        $path_for_save = WWW . $base_img_path;

        // Создаем директорию, если не существует
        $dir = dirname($path_for_save);
        if(!is_dir($dir)) {
            if(!mkdir($dir, 0755, true)) {
                $result['error'] = "Не удалось создать директорию для загрузки";
                return $result;
            }
        }

        // Перемещаем загруженный файл
        if(move_uploaded_file($file['tmp_name'], $path_for_save)) {
            // Меняем права доступа
            chmod($path_for_save, 0644);

            $result['success'] = true;
            $result['path'] = $base_img_path;
            $result['full_path'] = $path_for_save;
        } else {
            $result['error'] = "Ошибка при сохранении файла";
        }

        return $result;
    }

    /**
     * Возвращает человекочитаемое название поля для вывода в ошибках валидации.
     *
     * Используется маппинг технических имён полей → русские названия.
     * Если поле не найдено в словаре, возвращается исходное значение.
     *
     * @param string $field Техническое имя поля (например, 'meta_title', 'alias')
     *
     * @return string Человекочитаемое название (например, 'Meta Title', 'Alias')
     */
    private function getFieldName($field) {
        $names = [
            'alias' => 'Alias',
            'name' => 'Название',
            'meta_title' => 'Meta Title',
            'meta_desc' => 'Meta Description',
            'page_title' => 'Заголовок страницы',
            'page_desc' => 'Описание под заголовком',
            'text' => 'Текст поста'
        ];

        return $names[$field] ?? $field;
    }

    /**
     * Конвертирует код ошибки загрузки файла в текстовое сообщение на русском.
     *
     * Обрабатывает стандартные константы UPLOAD_ERR_* из PHP.
     * Для неизвестных кодов возвращает 'Неизвестная ошибка'.
     *
     * @param int $errorCode Код ошибки из $_FILES['*']['error'] (UPLOAD_ERR_*)
     *
     * @return string Описание ошибки на русском языке
     */
    private function getUploadError($errorCode) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'Файл превышает максимальный размер, указанный в php.ini',
            UPLOAD_ERR_FORM_SIZE => 'Файл превышает максимальный размер, указанный в форме',
            UPLOAD_ERR_PARTIAL => 'Файл был загружен только частично',
            UPLOAD_ERR_NO_FILE => 'Файл не был загружен',
            UPLOAD_ERR_NO_TMP_DIR => 'Отсутствует временная папка',
            UPLOAD_ERR_CANT_WRITE => 'Не удалось записать файл на диск',
            UPLOAD_ERR_EXTENSION => 'Загрузка файла была остановлена расширением PHP'
        ];

        return $errors[$errorCode] ?? 'Неизвестная ошибка';
    }
}