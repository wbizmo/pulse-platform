<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuilderTemplate extends Model
{
    protected $fillable = ['uuid', 'name', 'document', 'schema_version', 'created_by'];

    protected function casts(): array
    {
        return ['document' => 'array'];
    }
}
