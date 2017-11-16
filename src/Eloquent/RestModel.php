<?php

namespace Andrewlamers\EloquentRestBridge\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RestModel extends Model
{
    protected $connection = 'rest';

    public function insertAndSetId(Builder $query, $attributes)
    {
        parent::insertAndSetId($query, $attributes);

        $connection = $this->getConnection();

        if (method_exists($connection, 'getLastInsertId')) {
            $this->setAttribute($this->getKeyName(), $connection->getLastInsertId());
        }
    }
}