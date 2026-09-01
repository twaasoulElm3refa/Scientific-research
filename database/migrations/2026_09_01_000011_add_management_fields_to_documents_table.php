<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('source_id')->nullable()->index()->constrained()->restrictOnDelete();
            $table->foreignId('magazine_id')->nullable()->index()->constrained()->restrictOnDelete();
            $table->foreignId('document_type_id')->nullable()->index()->constrained()->restrictOnDelete();
            $table->foreignId('language_id')->nullable()->index()->constrained()->restrictOnDelete();
            $table->foreignId('category_id')->nullable()->index()->constrained()->restrictOnDelete();
            $table->foreignId('subcategory_id')->nullable()->index()->constrained()->restrictOnDelete();
            $table->uuid('submission_id')->nullable()->unique();
            $table->string('original_file_name', 500)->nullable();
            $table->string('file_extension', 20)->nullable();
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['source_id']);
            $table->dropForeign(['magazine_id']);
            $table->dropForeign(['document_type_id']);
            $table->dropForeign(['language_id']);
            $table->dropForeign(['category_id']);
            $table->dropForeign(['subcategory_id']);
            $table->dropIndex(['created_at']);
            $table->dropColumn([
                'source_id',
                'magazine_id',
                'document_type_id',
                'language_id',
                'category_id',
                'subcategory_id',
                'submission_id',
                'original_file_name',
                'file_extension',
            ]);
        });
    }
};
