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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained()->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reply_to_message_id')->nullable()->constrained('messages')->onDelete('set null');
            $table->foreignId('forward_from_message_id')->nullable()->constrained('messages')->onDelete('set null');
            $table->enum('message_type', ['text', 'image', 'video', 'audio', 'file', 'voice', 'location'])->default('text');
            $table->text('content')->nullable(); // text content
            $table->string('file_url')->nullable(); // for media messages
            $table->string('file_name')->nullable(); // original file name
            $table->bigInteger('file_size')->nullable(); // in bytes
            $table->integer('duration')->nullable(); // for audio/video in seconds
            $table->string('thumbnail_url')->nullable(); // for video/image thumbnails
            $table->boolean('is_edited')->default(false);
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();
            
            $table->index(['chat_id', 'created_at']);
            $table->index('sender_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
