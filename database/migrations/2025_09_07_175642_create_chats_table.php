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
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['private', 'group', 'channel']);
            $table->string('name')->nullable(); // group/channel name
            $table->text('description')->nullable(); // group/channel description
            $table->string('image_url')->nullable(); // group/channel image
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->boolean('is_public')->default(false); // for channels/groups
            $table->string('invite_link')->unique()->nullable(); // for public groups/channels
            $table->integer('max_members')->default(200); // member limit
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
