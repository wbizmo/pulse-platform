<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThemeActivation extends Model
{
    protected $table = 'theme_activation_history';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['previous_settings' => 'array', 'next_settings' => 'array'];
    }
}
