<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('specialization_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('country_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('title', 500);
            $table->string('subtitle', 500)->nullable();
            $table->text('abstract')->nullable();
            $table->text('description')->nullable();
            $table->string('document_type', 100)->nullable();
            $table->string('language', 100)->nullable();
            $table->integer('publication_year')->nullable();
            $table->date('publication_date')->nullable();
            $table->string('publisher')->nullable();
            $table->string('doi')->nullable();
            $table->string('isbn', 100)->nullable();
            $table->string('issn', 100)->nullable();
            $table->integer('total_pages')->nullable();
            $table->string('drive_file_id')->unique();
            $table->string('drive_file_name', 500)->nullable();
            $table->string('drive_folder_id')->nullable();
            $table->text('drive_url')->nullable();
            $table->string('mime_type', 150)->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->string('status', 50)->nullable();
            $table->boolean('is_active')->nullable()->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
