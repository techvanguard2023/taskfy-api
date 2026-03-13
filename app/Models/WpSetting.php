<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WpSetting extends Model
{
    protected $table = 'wp_settings';

    protected $fillable = [
        'user_id',
        'instance_id',
        'instance_name',
        'webhook_url',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
