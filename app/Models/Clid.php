<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clid extends Model
{
    protected $table = "clids";
    protected $fillable = [
        'id',
        'random_clid_list_id',
        'created_at',
        'updated_at',
        'order',
        'number',
    ];
}
