<?php

namespace App\Core\Exceptions;

/**
 * Базовый класс для пользовательских исключений приложения.
 *
 * Предназначен для расширения в конкретных классах ошибок.
 * Позволяет централизованно добавлять общие методы для обработки,
 * логирования или форматирования сообщений об исключениях.
 *
 * @package App\Core\Exceptions
 */
class AbstractException extends \Exception
{
    /**
     * Возвращает короткое имя класса исключения (без пространства имён).
     *
     * Полезно для маршрутизации логов: позволяет сохранять файлы ошибок
     * в отдельных директориях, соответствующих типу исключения.
     *
     * Пример: для \App\Core\Exceptions\DbException вернёт "DbException".
     *
     * @return string Короткое имя класса текущего экземпляра
     */
    public function getShortName(): string
    {
        $function = new \ReflectionClass(get_class($this));
        return $function->getShortName();
    }
}