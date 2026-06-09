<?php

namespace App\Services;

use App\Core\Exceptions\ErrorScriptException;

class Image
{
    public $data;
    public array $arr_allowed_tags = [
        't1200' => ['height' => 675, 'width' => 1200, 'text_layout' => 'top'],
        'm1200' => ['height' => 675, 'width' => 1200, 'text_layout' => 'middle'],
        'b1200' => ['height' => 675, 'width' => 1200, 'text_layout' => 'bottom'],
        '1200' => ['height' => 675, 'width' => 1200, 'text_layout' => 'none'],
        '640' => ['height' => 360, 'width' => 640, 'text_layout' => 'none'],
        's70' => ['height' => 70, 'width' => 70, 'text_layout' => 'none'],
        's300' => ['height' => 300, 'width' => 300, 'text_layout' => 'none'],
        's500' => ['height' => 500, 'width' => 500, 'text_layout' => 'none'],
        '400' => ['height' => 225, 'width' => 400, 'text_layout' => 'none'],
        'w300' => ['height' => 0, 'width' => 300, 'text_layout' => 'none'],
        'w600' => ['height' => 0, 'width' => 600, 'text_layout' => 'none'],
        '112' => ['height' => 63, 'width' => 112, 'text_layout' => 'none'],
        '208' => ['height' => 117, 'width' => 208, 'text_layout' => 'none'],
    ];
    public static function getAllowedTags() {
        $one = new self();
        $arr_tags = $one->arr_allowed_tags;
        return array_keys($arr_tags);
    }
    public function addMultilineCenteredText(array $strings) {
        if (empty($strings)) return;

        if (!extension_loaded('gd') && !extension_loaded('gd2')) {
            throw new ErrorScriptException('GD extension is required');
        }

        $img = imagecreatefromstring($this->data);
        if (!$img) {
            throw new ErrorScriptException('Ошибка загрузки изображения для добавления текста');
        }

        imagealphablending($img, true);
        imagesavealpha($img, true);

        $width = imagesx($img);
        $height = imagesy($img);

        $font_path = WWW . '/assets/font.ttf'; //
        $text_color = imagecolorallocate($img, 255, 255, 255); // Основной белый цвет

        $max_text_width = $width * 0.9;
        $line_spacing = 15;

        // Базовые размеры шрифта
        $base_font_sizes = [75, 55, 40, 30, 24, 20];

        $lines_data = [];
        $total_height = 0;

        // Шаг 1: Просчитываем каждую строку
        foreach ($strings as $index => $text) {
            $font_size = isset($base_font_sizes[$index]) ? $base_font_sizes[$index] : 18;

            $bbox = imagettfbbox($font_size, 0, $font_path, $text);
            $text_width = $bbox[2] - $bbox[0];

            while ($text_width > $max_text_width && $font_size > 12) {
                $font_size--;
                $bbox = imagettfbbox($font_size, 0, $font_path, $text);
                $text_width = $bbox[2] - $bbox[0];
            }

            $text_height = $bbox[1] - $bbox[7];

            $lines_data[] = [
                'text'      => $text,
                'font_size' => $font_size,
                'width'     => $text_width,
                'height'    => $text_height,
                'bbox'      => $bbox
            ];

            $total_height += $text_height;
        }

        $total_height += (count($lines_data) - 1) * $line_spacing;

        // Шаг 2: Вычисляем начальную Y координату
        $current_y = ($height - $total_height) / 2;

        // Настройки свечения / тени
        $glow_radius = 8; // Радиус свечения в пикселях (можно менять)

        // Шаг 3: Отрисовываем каждую строку
        foreach ($lines_data as $line) {
            $x = ($width - $line['width']) / 2;
            $y = $current_y + abs($line['bbox'][7]);

            // Отрисовка свечения (тени) от большего радиуса к меньшему
            // В GD альфа-канал идет от 0 (непрозрачный) до 127 (полностью прозрачный)
            for ($r = $glow_radius; $r >= 1; $r--) {
                // Вычисляем альфа-канал: края будут более прозрачными (около 120), ближе к тексту плотнее (около 80)
                $alpha = 127 - (int)((8 / $glow_radius) * ($glow_radius - $r + 1));
                if ($alpha > 127) $alpha = 127;
                if ($alpha < 0) $alpha = 0;

                $shadow_color = imagecolorallocatealpha($img, 0, 0, 0, $alpha);

                // Отрисовка по кругу для создания равномерного контура (8 направлений)
                imagettftext($img, $line['font_size'], 0, $x + $r, $y + $r, $shadow_color, $font_path, $line['text']);
                imagettftext($img, $line['font_size'], 0, $x + $r, $y - $r, $shadow_color, $font_path, $line['text']);
                imagettftext($img, $line['font_size'], 0, $x - $r, $y + $r, $shadow_color, $font_path, $line['text']);
                imagettftext($img, $line['font_size'], 0, $x - $r, $y - $r, $shadow_color, $font_path, $line['text']);
                imagettftext($img, $line['font_size'], 0, $x, $y + $r, $shadow_color, $font_path, $line['text']);
                imagettftext($img, $line['font_size'], 0, $x, $y - $r, $shadow_color, $font_path, $line['text']);
                imagettftext($img, $line['font_size'], 0, $x + $r, $y, $shadow_color, $font_path, $line['text']);
                imagettftext($img, $line['font_size'], 0, $x - $r, $y, $shadow_color, $font_path, $line['text']);
            }

            // Отрисовка основного белого текста поверх тени
            imagettftext($img, $line['font_size'], 0, $x, $y, $text_color, $font_path, $line['text']);

            // Сдвигаем Y для следующей строки
            $current_y += $line['height'] + $line_spacing;
        }

        // Пересохраняем изображение в буфер
        ob_start();
        imagewebp($img, null, 95);
        $this->data = ob_get_clean();

        imagedestroy($img);
    }
    public function addOverlay($overlay_path) {
        if (!file_exists($overlay_path)) {
            throw new ErrorScriptException('Файл для наложения не найден: ' . $overlay_path);
        }

        // Загружаем базовое изображение из текущих данных (оно там в webp после resize)
        $baseImage = imagecreatefromstring($this->data);
        if (!$baseImage) {
            throw new ErrorScriptException('Ошибка загрузки базового изображения для наложения');
        }

        // Загружаем PNG слой (затемнение или вотермарку)
        $overlayImage = imagecreatefrompng($overlay_path);
        if (!$overlayImage) {
            throw new ErrorScriptException('Ошибка загрузки PNG слоя');
        }

        // Включаем режим смешивания для правильного отображения прозрачности (альфа-канала)
        imagealphablending($baseImage, true);
        imagesavealpha($baseImage, true);

        $baseWidth = imagesx($baseImage);
        $baseHeight = imagesy($baseImage);

        $overlayWidth = imagesx($overlayImage);
        $overlayHeight = imagesy($overlayImage);

        // Накладываем изображение. Используем imagecopyresampled на случай,
        // если пропорции базовой картинки и слоя (1200x675) минимально расходятся
        imagecopyresampled(
            $baseImage, $overlayImage,
            0, 0, 0, 0,
            $baseWidth, $baseHeight,
            $overlayWidth, $overlayHeight
        );

        // Пересохраняем результат в буфер
        ob_start();
        imagewebp($baseImage, null, 95);
        $this->data = ob_get_clean();

        // Очищаем память
        imagedestroy($baseImage);
        imagedestroy($overlayImage);
    }

    /**
     * @throws ErrorScriptException
     */
    public function saveFile($path) {
        if(!is_dir(pathinfo($path, PATHINFO_DIRNAME))) {
            umask(0);
            mkdir(pathinfo($path, PATHINFO_DIRNAME), 0777, true);
            chmod(pathinfo($path, PATHINFO_DIRNAME), 0777);
        }
        file_put_contents($path, $this->data);
        if(file_exists($path)) {
            umask(0);
            chmod($path, 0775);
        } else {
            throw new ErrorScriptException('Не смогли сохранить файл');
        }
    }
    public function addText($text, $text_layout) {
        if (!extension_loaded('gd') && !extension_loaded('gd2')) {
            throw new ErrorScriptException('GD extension is required');
        }

        $original = imagecreatefromstring($this->data);

        if (!$original) {
            throw new ErrorScriptException('Ошибка загрузки изображения для добавления текста');
        }


        $width = imagesx($original);
        $height = imagesy($original);
        $max_text_width = $width * 0.9;
        $font_path = WWW . '/assets/font.ttf';
        $min_font_size = 30; // Минимальный размер шрифта
        $font_size = 60; // Начальный размер шрифта
        $text_color = imagecolorallocate($original, 255, 255, 255);
        $line_spacing = 0.3; // Интервал между строками (30% от размера шрифта)



        // Подбор размера шрифта с учетом минимального
        $bbox = imagettfbbox($font_size, 0, $font_path, $text);
        $text_width = $bbox[2] - $bbox[0];
        while ($text_width > $max_text_width && $font_size > $min_font_size) {
            $font_size--;
            $bbox = imagettfbbox($font_size, 0, $font_path, $text);
            $text_width = $bbox[2] - $bbox[0];
        }

        // Проверка необходимости переноса строк
        $lines = [$text];
        if ($font_size == $min_font_size && $text_width > $max_text_width) {
            $lines = $this->wrap_text($text, $font_size, $font_path, $max_text_width);
        }

        // Расчет размеров текстового блока
        $line_height = 0;
        $text_block_height = 0;
        $text_width = 0;
        foreach ($lines as $line) {
            $bbox = imagettfbbox($font_size, 0, $font_path, $line);
            $line_width = $bbox[2] - $bbox[0];
            $line_height = $bbox[1] - $bbox[7];
            $text_width = max($text_width, $line_width);
            $text_block_height += $line_height;
        }
        $text_block_height += ($line_height * $line_spacing) * (count($lines) - 1);

        // Расчет отступов
        $padding = $font_size * 0.5;
        $total_height = $text_block_height + 2 * $padding;
        $overlay_color = imagecolorallocatealpha($original, 0, 0, 0, 80);

        // Создание картинки

        $img = imagecreatetruecolor($width, $height);
        imagesavealpha($img, true);
        imagefill($img, 0, 0, imagecolorallocatealpha($img, 0, 0, 0, 127));
        imagecopy($img, $original, 0, 0, 0, 0, $width, $height);

        // Расчет позиции
        switch ($text_layout) {
            case 'top':
                $y_rect = 30;
                $text_y = $y_rect + $padding + $line_height;
                break;
            case 'middle':
                $y_rect = ($height - $total_height) / 2;
                $text_y = $y_rect + $padding + $line_height;
                break;
            case 'bottom':
                $y_rect = $height - 30 - $total_height;
                $text_y = $y_rect + $padding + $line_height;
                break;
        }

        // Рисуем подложку
        imagefilledrectangle(
            $img,
            0,
            $y_rect,
            $width,
            $y_rect + $total_height,
            $overlay_color
        );

        // Рисуем текст по строкам
        foreach ($lines as $i => $line) {
            // Получаем размеры текущей строки
            $bbox = imagettfbbox($font_size, 0, $font_path, $line);
            $current_line_width = $bbox[2] - $bbox[0];
            $current_line_height = $bbox[1] - $bbox[7];

            // Центрируем каждую строку отдельно
//            $text_x = ($width - $current_line_width) / 2;
//            $y_offset = $text_y + ($current_line_height * (1 + $line_spacing)) * $i;
            $text_x = (int)round(($width - $current_line_width) / 2); // <-- Добавлено округление
            $y_offset = (int)round(
                $text_y + ($current_line_height * (1 + $line_spacing)) * $i
            ); // <-- Добавлено округление

            imagettftext($img, $font_size, 0, $text_x, $y_offset, $text_color, $font_path, $line);
        }


        // Сохранение в буфер
        ob_start();
        imagewebp($img, null, 80);
        $image_data = ob_get_clean();
        $this->data = $image_data;

        imagedestroy($img);
        imagedestroy($original);

    }
    public function wrap_text($text, $font_size, $font_path, $max_width) {
        $words = explode(' ', $text);
        $lines = [];
        $current_line = '';

        foreach ($words as $word) {
            $test_line = $current_line ? $current_line . ' ' . $word : $word;
            $bbox = imagettfbbox($font_size, 0, $font_path, $test_line);
            $test_width = $bbox[2] - $bbox[0];

            if ($test_width <= $max_width) {
                $current_line = $test_line;
            } else {
                $lines[] = $current_line;
                $current_line = $word;
            }
        }
        $lines[] = $current_line;
        return $lines;
    }
    public function getTagArr($tag) {
        $arr_allowed_tags = $this->arr_allowed_tags;
        $keys_tags = array_keys($this->arr_allowed_tags);
        if(in_array($tag, $keys_tags)) {
            return $arr_allowed_tags[$tag];
        } else {
            return false;
        }
    }
    public function showImage() {
        header('Content-Type: image/webp');
        echo $this->data;
        exit;
    }
    public function resize($img_path, $width, $height) {
        $sourceImage = null;
        $imageInfo = getimagesize($img_path);

        if (!$imageInfo) {
            throw new ErrorScriptException('Файл не является изображением или поврежден');
        }

        $img_type = $imageInfo['mime'];
        switch ($img_type) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($img_path);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($img_path);
                break;
            case 'image/webp':
                $sourceImage = imagecreatefromwebp($img_path);
                break;
            default:
                throw new ErrorScriptException('Ошибка определения исходного изображения');
        }

        if (!$sourceImage) {
            throw new ErrorScriptException('Ошибка создания исходного изображения');
        }

        // Получаем оригинальные размеры
        $originalWidth = imagesx($sourceImage);
        $originalHeight = imagesy($sourceImage);

        // Обработка случаев, когда ширина или высота равна 0
        if ($width == 0 && $height == 0) {
            throw new ErrorScriptException('Ширина и высота не могут быть одновременно 0');
        }

        if ($width == 0) {
            // Вычисляем ширину на основе высоты
            $width = (int) round(($originalWidth / $originalHeight) * $height);
        } elseif ($height == 0) {
            // Вычисляем высоту на основе ширины
            $height = (int) round(($originalHeight / $originalWidth) * $width);
        }

        if ($width <= 0 || $height <= 0) {
            throw new ErrorScriptException('Некорректные размеры после вычисления');
        }

        // Проверяем, нужно ли применять центрирование при небольшой погрешности
        $allowCentering = false;
        $cropOffsetY = 0;

        if ($width == 1200 && $height == 675 && $originalWidth == 1200) {
            if ($originalHeight >= 675 && $originalHeight <= 705) { // погрешность 30 пикселей
                $allowCentering = true;
                $cropOffsetY = (int) round(($originalHeight - 675) / 2);
            }
        }

        if ($allowCentering) {
            // Создаем новое изображение с целевыми размерами
            $newImage = imagecreatetruecolor($width, $height);

            // Копируем часть исходного изображения с центрированием по вертикали
            imagecopyresampled(
                $newImage,
                $sourceImage,
                0, 0,          // целевые координаты
                0, $cropOffsetY, // исходные координаты (обрезаем по центру)
                $width, $height, // целевые размеры
                $width, $height  // исходные размеры (берем только нужную часть)
            );
        } else {
            // Старая логика - масштабирование с искажением пропорций
            $newImage = imagecreatetruecolor($width, $height);
            imagecopyresampled(
                $newImage,
                $sourceImage,
                0, 0, 0, 0,
                $width, $height,
                $originalWidth, $originalHeight
            );
        }

        ob_start();
        imagewebp($newImage, null, 95);
        $this->data = ob_get_clean();

        imagedestroy($newImage);
        imagedestroy($sourceImage);
    }

    public function getWidth($tag) {
        $arr_allowed_tags = $this->arr_allowed_tags;
        if(isset($arr_allowed_tags[$tag])) {
            return $arr_allowed_tags[$tag]['width'];
        }
    }
    public function getHeight($tag) {
        $arr_allowed_tags = $this->arr_allowed_tags;
        if(isset($arr_allowed_tags[$tag])) {
            return $arr_allowed_tags[$tag]['height'];
        }
    }

    public function checkAllowedTag($tag) {
        $keys_tags = array_keys($this->arr_allowed_tags);
        if(in_array($tag, $keys_tags)) {
            return true;
        } else {
            return false;
        }
    }
}