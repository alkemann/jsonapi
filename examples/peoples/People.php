<?php

namespace app;

use alkemann\jsonapi\Model;

/**
 * This assumes you have a connection called `default` that connects
 * to a datasource that has a table called 'people' with the columns
 * 'id', 'name', 'age' in it.
 */
class People extends Model
{
    static $table = 'people';
    static $fields = ['id', 'name', 'age'];
}
