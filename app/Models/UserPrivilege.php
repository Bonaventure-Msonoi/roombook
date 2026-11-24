<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPrivilege extends Model
{
    protected $fillable = [
        'user_id',
        'privilege',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}