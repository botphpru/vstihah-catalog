<?php

namespace App\Controllers;

use App\Controllers\FrontendController;
use App\Core\Exceptions\NotFoundException;

class ImageController extends FrontendController
{
    private array $path_obj = [
        'blogpost' => \App\Models\BlogPost::class,
    ];

    public function getCatalogImage(int $event_id, string $img_tag, string $encoded)
    {
        if ($event_id < 0 || !preg_match('/^e\d+-r\d+-n\d+-g\d+$/', $encoded)) {
            throw new NotFoundException('Некорректные параметры изображения');
        }

        // Парсим контекст
        $contextIds = $this->decodeContext($encoded);

        // 2. Проверяем, что event_id в URL совпадает с закодированным (защита от подмены)
        if ($contextIds['event'] !== null && $contextIds['event'] !== $event_id) {
            throw new NotFoundException('не совпадает id event при генерации изображения');
        }
        $context = [
            'event' => null,
            'recipient' => null,
            'name' => null,
            'genre' => null
        ];
        if($contextIds['event'] != null) {
            $event = \App\Models\Event::getById($contextIds['event'], true);
            if(!$event) throw new NotFoundException('неправильный id event');
            $context['event'] = $event;
        }
        if($contextIds['recipient'] != null) {
            $recipient = \App\Models\Recipient::getById($contextIds['recipient'], true);
            if(!$recipient) throw new NotFoundException('неправильный id recipient');
            $context['recipient'] = $recipient;
        }
        if($contextIds['name'] != null) {
            $name = \App\Models\Name::getById($contextIds['name'], true);
            if(!$name) throw new NotFoundException('неправильный id name');
            $context['name'] = $name;
        }
        if($contextIds['genre'] != null) {
            $genre = \App\Models\Genre::getById($contextIds['genre'], true);
            if(!$genre) throw new NotFoundException('неправильный id genre');
            $context['genre'] = $genre;
        }
        if(empty($context)) throw new NotFoundException('Почему то все null в context');
        $text = $this->buildImageText($context);
        $array_strings = [$text];
        $image = new \App\Services\Image();
        if(!$image->checkAllowedTag($img_tag)) throw new NotFoundException('неправильный тег у изображения');
        $placeholder = WWW.'/images/ph-catalog.jpg';

        //делаем ресайз на 1200х630
        $image->resize($placeholder, 1200, 630);

        $image->addText($text, 'middle');


        $server_new_file_path = WWW.'/images/catalog/'.$event_id.'/'.$img_tag.'_'.$encoded.'.webp';
        //сохраняем файл
        $image->saveFile($server_new_file_path);

        //выводим в браузер результат
        $image->showImage();
    }

    public function getImage($dir_ob, $entity_id, $img_tag, $md5_id) {
        $image = new \App\Services\Image();
        if(!isset($this->path_obj[$dir_ob])) throw new NotFoundException('нету такой dir изображения в разрешенных');
        $entity = $this->path_obj[$dir_ob]::getById($entity_id);
        if(!$entity) throw new NotFoundException('попытка открыть изображение у которого нету entity');


        $server_new_file_path = WWW.'/images/'.$dir_ob.'/'.$entity->id.'/'.$img_tag.'_'.$md5_id.'.webp';

        if(isset($entity->img) and $entity->img != null) {
            $img_original_path = WWW.$entity->img;
        } elseif(file_exists(WWW.'/images/placeholder.jpg')) {
            //нету изображения.
            //пытаемся найти placeholder
            $img_original_path = WWW.'/images/placeholder.jpg';
        } else {
            throw new NotFoundException('нету placeholder изображения и нету оригинала');
        }
        $tag_arr = $image->getTagArr($img_tag);
        //делаем ресайз изображения
        $image->resize($img_original_path, $image->getWidth($img_tag), $image->getHeight($img_tag));

        //при необходимости добавляем текст
        if($tag_arr['text_layout'] != 'none') {
            $text = isset($entity->short_title) ? $entity->short_title : $entity->name;
            if($text and $text != '') {
                $image->addText($text, $tag_arr['text_layout']);
            }
        }

        //сохраняем файл
        $image->saveFile($server_new_file_path);

        //выводим в браузер результат
        $image->showImage();

    }


    private function buildImageText(array $context): string
    {
        $parts = ['Стихи поздравления'];

        // Порядок для H1: genre → event → recipient → name
        $order = ['genre', 'event', 'recipient', 'name'];

        foreach ($order as $key) {
            if ($context[$key] !== null) {
                $phrase = !empty($context[$key]->phrase)
                    ? $context[$key]->phrase
                    : $context[$key]->name;
                $parts[] = $phrase;
            }
        }

        return implode(' ', $parts);
    }
}