<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Media extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'original_name',
        'file_name',
        'mime_type',
        'extension',
        'disk',
        'path',
        'url',
        'size',
        'type',
        'alt_text',
        'caption',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getReadableSizeAttribute(): string
    {
        if ($this->size >= 1048576) {
            return round($this->size / 1048576, 2).' MB';
        }

        if ($this->size >= 1024) {
            return round($this->size / 1024, 2).' KB';
        }

        return $this->size.' B';
    }
}
