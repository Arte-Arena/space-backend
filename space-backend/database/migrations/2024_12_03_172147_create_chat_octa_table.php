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
        Schema::create('chat_octa', function (Blueprint $table) {
            $table->id();
            $table->string('octa_id');
            $table->string('number');
            $table->string('channel')->nullable();
            $table->string('contact_id')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('agent_id')->nullable();
            $table->string('agent_name')->nullable();
            $table->string('agent_email')->nullable();
            $table->string('lastMessageDate')->nullable();
            $table->string('status')->nullable();
            $table->string('closedAt')->nullable();
            $table->string('group_id')->nullable();
            $table->string('group_name')->nullable();
            $table->json('tags')->nullable();
            $table->boolean('withBot')->nullable();
            $table->integer('unreadMessages')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_octa');
    }
};
