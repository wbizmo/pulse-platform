<?php

namespace App\Rules;

use App\Domain\Content\ReservedSlug;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class AvailableContentSlug implements ValidationRule
{
    public function __construct(private readonly string $table, private readonly ?int $ignoreId = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $slug = ReservedSlug::normalize((string) $value);
        if ($slug === '' || ReservedSlug::contains($slug)) {
            $fail('The :attribute is blank or reserved by the system.');

            return;
        }

        $query = DB::table($this->table)->whereRaw('LOWER(slug) = ?', [strtolower($slug)]);
        if ($this->ignoreId !== null) {
            $query->where('id', '!=', $this->ignoreId);
        }
        if ($query->exists()) {
            $fail('The :attribute has already been taken.');
        }
    }
}
