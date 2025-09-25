<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('abstract')->nullable();
            $table->string('document_number')->nullable()->unique();
            $table->string('call_number')->nullable();
            $table->string('teu_number')->nullable(); // Terbitan, Edisi, Update number
            
            // Relationships
            $table->foreignId('document_type_id')->constrained()->cascadeOnUpdate();
            $table->foreignId('document_status_id')->constrained()->cascadeOnUpdate();
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate();
            $table->foreignId('updated_by')->nullable()->constrained('users')->cascadeOnUpdate();
            
            // Document specific fields
            $table->string('language', 10)->default('id');
            $table->text('content')->nullable();
            $table->text('note')->nullable();
            $table->string('source')->nullable();
            $table->string('location')->nullable();
            
            // JDIHN Compliance fields
            $table->json('jdihn_metadata')->nullable();
            $table->timestamp('jdihn_last_sync')->nullable();
            $table->string('jdihn_status')->nullable();
            $table->string('jdihn_id')->nullable();
            
            // Publishing dates
            $table->date('published_date')->nullable();
            $table->date('effective_date')->nullable();
            $table->date('expired_date')->nullable();
            
            // SEO and public fields
            $table->string('slug')->unique();
            $table->text('meta_description')->nullable();
            $table->text('keywords')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->integer('view_count')->default(0);
            $table->integer('download_count')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['document_type_id', 'document_status_id']);
            $table->index(['published_date', 'is_featured']);
            $table->index(['created_by', 'created_at']);
            $table->index('jdihn_id');
            $table->fullText(['title', 'abstract', 'content']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
