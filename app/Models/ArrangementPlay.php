<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArrangementPlay extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    public function arrangement(): BelongsTo
    {
        return $this->belongsTo(Arrangement::class);
    }
}
