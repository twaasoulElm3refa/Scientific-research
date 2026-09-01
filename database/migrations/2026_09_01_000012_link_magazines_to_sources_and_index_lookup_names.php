<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->index('name');
        });

        Schema::table('subcategories', function (Blueprint $table) {
            $table->index('name');
        });

        Schema::table('specializations', function (Blueprint $table) {
            $table->index('name');
        });

        Schema::table('magazines', function (Blueprint $table) {
            $table->foreignId('source_id')
                ->nullable()
                ->after('id')
                ->index()
                ->constrained()
                ->restrictOnDelete();

            $table->dropUnique(['name']);
            $table->unique(['source_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::table('magazines', function (Blueprint $table) {
            $table->dropUnique(['source_id', 'name']);
            $table->dropForeign(['source_id']);
            $table->dropIndex(['source_id']);
            $table->dropColumn('source_id');
            $table->unique('name');
        });

        Schema::table('specializations', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });

        Schema::table('subcategories', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });
    }
};
