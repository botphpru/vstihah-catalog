<?php

namespace App\Models;

use App\Core\Relation;

class RelationPoemRecipient extends Relation
{

    protected static function getTableName(): string
    {
        return 'poem_recipient';
    }
}