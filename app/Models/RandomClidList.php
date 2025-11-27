<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RandomClidList extends Model
{
    protected $table = "random_clid_lists";
    protected $fillable = [
        'id',
        'name',
        'created_at',
        'updated_at',
    ];

    public function clids()
    {
        return $this->hasMany(Clid::class, 'random_clid_list_id', 'id');
    }
}
