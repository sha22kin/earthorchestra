<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Citation extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['position' => 'integer'];
    }

    public function visualisation(): BelongsTo
    {
        return $this->belongsTo(Visualisation::class);
    }
}
