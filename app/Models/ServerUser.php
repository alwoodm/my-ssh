<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServerUser extends Model
{
    protected $guarded = [];

    protected $casts = [
        'password' => 'encrypted',
        'last_login_at' => 'datetime',
    ];

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }
}
