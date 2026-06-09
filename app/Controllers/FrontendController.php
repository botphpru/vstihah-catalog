<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\View;

abstract class FrontendController extends Controller
{
    public array $vars;

    protected const CONTEXT_KEYS = ['event' => 'e', 'recipient' => 'r', 'name' => 'n', 'genre' => 'g'];
    protected const CANONICAL_ORDER = ['event', 'recipient', 'name', 'genre'];

    public function __construct(View $view, Request $request)
    {
        $this->view = $view;
        $this->request = $request;
        $this->setDefaultLayout();
        $this->setFrontedVars();
    }

    /**
     * Кодирует контекст в строку: e1-r0-n5-g0
     */
    protected function encodeContext(array $context): string
    {
        $parts = [];
        foreach (self::CANONICAL_ORDER as $key) {
            $code = self::CONTEXT_KEYS[$key];
            $value = ($context[$key] !== null) ? (int)$context[$key]->id : 0;
            $parts[] = "{$code}{$value}";
        }
        return implode('-', $parts);
    }

    /**
     * Декодирует строку обратно в массив ID
     */
    protected function decodeContext(string $encoded): array
    {
        $map = array_flip(self::CONTEXT_KEYS);
        $result = array_fill_keys(self::CANONICAL_ORDER, null);

        $parts = explode('-', $encoded);
        foreach ($parts as $part) {
            $code = $part[0]; // первая буква: e, r, n, g
            $value = (int)substr($part, 1); // остальное: число
            $key = $map[$code] ?? null;
            if ($key && $value !== 0) {
                $result[$key] = $value;
            }
        }
        return $result;
    }



    protected function setFrontedVars()
    {
        $result = [
            'total_count_poems' => \App\Models\Poem::getCountTotal(true),//кол-во стихов
            'total_count_events' => \App\Models\Event::getCountTotal(true),//кол-во событий
            'total_count_male_names' => \App\Models\Name::getCountByArr(['gender' => 'male'], true),//кол-во мужских имен
            'total_count_female_names' => \App\Models\Name::getCountByArr(['gender' => 'female'], true),//кол-во женских имен
            'total_count_genres' => \App\Models\Genre::getCountTotal(true),//кол-во жанров
            'total_count_recipients' => \App\Models\Recipient::getCountTotal(true),//кол-во получателей
        ];
        $this->vars = $result;
    }
    protected function setDefaultLayout(): void
    {
        $this->setLayout('default');
    }


}