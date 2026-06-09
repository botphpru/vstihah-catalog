<?php

namespace App\Models;

use App\Core\Relation;

class RelationPoemGenre extends Relation
{

    protected static function getTableName(): string
    {
        return 'poem_genre';
    }
}