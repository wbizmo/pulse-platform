<?php

use App\Domain\Content\Taxonomy;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['categories', 'tags'] as $table) {
            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->string('normalized_name', Taxonomy::MAX_NAME_LENGTH)->nullable()->after('name');
            });
            DB::table($table)->orderBy('id')->eachById(function (object $row) use ($table): void {
                DB::table($table)->where('id', $row->id)->update(['normalized_name' => Taxonomy::normalizeName($row->name)]);
            });
            Schema::table($table, function (Blueprint $blueprint) use ($table): void {
                $blueprint->string('normalized_name', Taxonomy::MAX_NAME_LENGTH)->nullable(false)->change();
                $blueprint->unique('normalized_name', $table.'_normalized_name_unique');
            });
        }
    }

    public function down(): void
    {
        foreach (['categories', 'tags'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table): void {
                $blueprint->dropUnique($table.'_normalized_name_unique');
                $blueprint->dropColumn('normalized_name');
            });
        }
    }
};
