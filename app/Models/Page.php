<?php

namespace App\Models;

use App\Core\Model;

class Page extends Model
{
    public string $alias;
    public string $short_title;
    public string $meta_title;
    public string $meta_desc;
    public string $page_title;
    protected static function getTableName(): string
    {
        return 'pages';
    }
}