<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutboundRoute extends Model
{
    use HasFactory;

    protected $table = "outbound_routes";

    protected $fillable = [
        'id',
        'companie_id',
        'created_at',
        'updated_at',
        'voip_account_id',
        'voip_trunk_id',
        'random_clid_list_id',
        'recording',
    ];
    
    public function account()
    {
        return $this->belongsTo(VoipAccount::class, 'voip_account_id');
    }
    public function trunk()
    {
        return $this->belongsTo(VoipTrunk::class, 'voip_trunk_id');
    }
    public function caller()
    {
        return $this->belongsTo(RandomClidList::class, 'random_clid_list_id');
    }


}
