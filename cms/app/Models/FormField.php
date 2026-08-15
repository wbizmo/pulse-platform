<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormField extends Model
{
    protected $fillable = ['key', 'label', 'type', 'help', 'placeholder', 'required', 'sort_order', 'configuration'];

    protected $casts = ['required' => 'boolean', 'configuration' => 'array'];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
}
