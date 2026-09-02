<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('license_type_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('rights_status_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('url', 2048)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['license_type_id']);
            $table->dropForeign(['rights_status_id']);
            $table->dropColumn(['license_type_id', 'rights_status_id', 'url']);
        });
    }
};
