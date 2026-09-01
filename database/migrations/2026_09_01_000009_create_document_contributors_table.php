<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_contributors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('contributor_id')->index()->constrained()->cascadeOnDelete();
            $table->string('role', 100)->nullable();
            $table->integer('contributor_order')->nullable();
            $table->unique(['document_id', 'contributor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_contributors');
    }
};
