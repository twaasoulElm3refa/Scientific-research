<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->index('name');
        });

        Schema::table('authors', function (Blueprint $table) {
            $table->index('name');
        });

        Schema::table('contributors', function (Blueprint $table) {
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::table('contributors', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });

        Schema::table('authors', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });
    }
};
