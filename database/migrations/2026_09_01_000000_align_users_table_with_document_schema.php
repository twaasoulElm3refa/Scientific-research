<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $nameUniqueIndex = collect(Schema::getIndexes('users'))->first(
            fn (array $index): bool => ($index['unique'] ?? false)
                && ($index['columns'] ?? []) === ['name']
        );

        if ($nameUniqueIndex !== null) {
            Schema::table('users', function (Blueprint $table) use ($nameUniqueIndex) {
                $table->dropUnique($nameUniqueIndex['name']);
            });
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE "users" DROP CONSTRAINT IF EXISTS "users_role_check"');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 50)->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $hasNameUniqueIndex = collect(Schema::getIndexes('users'))->contains(
            fn (array $index): bool => ($index['unique'] ?? false)
                && ($index['columns'] ?? []) === ['name']
        );

        Schema::table('users', function (Blueprint $table) use ($hasNameUniqueIndex) {
            $table->string('role')->nullable()->default('user')->change();

            if (! $hasNameUniqueIndex) {
                $table->unique('name');
            }
        });
    }
};
