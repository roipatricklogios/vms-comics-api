<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Authors extends Model
{
        /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $fillable = [
        'first_name ', 'last_name', 'thumbnail_url'
    ];
}
