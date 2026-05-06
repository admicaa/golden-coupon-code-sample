<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes used by the search and facet code paths.
 *
 * `country_names.name` / `header_name` and `search_options_pages.name` are hit
 * with `whereIn(...)` and equality lookups on every search request.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('country_names', function (Blueprint $table) {
            $table->index('name', 'country_names_name_idx');
            $table->index('header_name', 'country_names_header_name_idx');
        });

        Schema::table('search_options_pages', function (Blueprint $table) {
            $table->index('name', 'search_options_pages_name_idx');
        });
    }

    public function down(): void
    {
        Schema::table('country_names', function (Blueprint $table) {
            $table->dropIndex('country_names_name_idx');
            $table->dropIndex('country_names_header_name_idx');
        });

        Schema::table('search_options_pages', function (Blueprint $table) {
            $table->dropIndex('search_options_pages_name_idx');
        });
    }
};
