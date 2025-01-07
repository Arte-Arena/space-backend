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
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255); // Limite de 255 caracteres
            $table->text('description')->nullable();
            $table->enum('category', [
                'evento',
                'feriado_nacional',
                'feriado_estadual',
                'feriado_municipal',
                'ponto_facultativo_externo',
                'ponto_facultativo_interno'
            ]);
            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Chave única para evitar duplicações
            $table->unique(['title', 'start_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
