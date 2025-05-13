<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $table = "patients";
    protected $fillable = [
        "medium_acquisition",
        "user_id",
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
