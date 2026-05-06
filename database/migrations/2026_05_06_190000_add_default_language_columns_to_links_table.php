<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('links')) {
            return;
        }

        $shortcuts = array_values(array_unique(array_filter(language_shortcuts())));

        Schema::table('links', function (Blueprint $table) use ($shortcuts) {
            foreach ($shortcuts as $shortcut) {
                $column = 'name__' . strtoupper($shortcut);

                if (!Schema::hasColumn('links', $column)) {
                    $table->string($column)->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        // Intentionally left blank. Existing installations may already have
        // these columns from earlier runs, so rollback must stay non-destructive.
    }
};
