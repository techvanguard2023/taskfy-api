<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WpSetting extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'instance_id',
        'instance_name',
        'webhook_url',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
