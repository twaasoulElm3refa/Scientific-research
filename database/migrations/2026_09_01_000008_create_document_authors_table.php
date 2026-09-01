<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_authors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->index()->constrained()->cascadeOnDelete();
            $table->integer('author_order')->nullable();
            $table->unique(['document_id', 'author_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_authors');
    }
};
