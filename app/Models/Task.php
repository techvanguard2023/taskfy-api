<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Task extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'priority',
        'completed',
        'completed_at',
    ];

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
