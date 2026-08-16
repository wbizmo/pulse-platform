<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Existing rows are retained as inert records so settings/history are not destroyed.
        DB::table('plugins')->whereNotIn('slug', ['editorial-notes', 'publishing-insights'])->update(['is_active' => false]);
    }

    public function down(): void
    {
        // Retirement is intentionally non-destructive and cannot safely infer prior activation.
    }
};
