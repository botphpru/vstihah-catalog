<?php

namespace App\Models;

use App\Core\Relation;

class RelationPoemName extends Relation
{

    protected static function getTableName(): string
    {
        return 'poem_name';
    }
}