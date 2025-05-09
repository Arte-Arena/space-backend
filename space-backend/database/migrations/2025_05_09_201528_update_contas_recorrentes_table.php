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
        Schema::table('contas_recorrentes', function (Blueprint $table) {
            // Renomear campos existentes para manter compatibilidade
            $table->renameColumn('periodo_recorrencia', 'intervalo');
            $table->renameColumn('data_proxima_recorrencia', 'data_inicio');

            // Adicionar novos campos ENUM (MySQL)
            $table->enum('tipo_recorrencia', [
                'diaria',
                'semanal',
                'quinzenal',
                'mensal',
                'bimestral',
                'trimestral',
                'semestral',
                'anual',
                'personalizada'
            ])
                ->default('mensal')
                ->after('conta_id');

            $table->enum('status', ['ativa', 'pausada', 'cancelada'])
                ->default('ativa')
                ->nullable()
                ->after('recorrencias_restantes');

            // Adicionar campos específicos de recorrência
            $table->integer('dia_mes')
                ->nullable()
                ->comment('Dia específico do mês para recorrências mensais')
                ->after('intervalo');

            $table->enum('dia_semana', ['seg', 'ter', 'qua', 'qui', 'sex', 'sab', 'dom'])
                ->nullable()
                ->comment('Dia da semana para recorrências semanais')
                ->after('dia_mes');

            // Renomear e ajustar campo existente
            $table->renameColumn('recorrencias_restantes', 'max_ocorrencias');
            $table->integer('max_ocorrencias')->nullable()->change();

            // Adicionar campo de data fim
            $table->date('data_fim')
                ->nullable()
                ->comment('Data final para recorrência com término específico')
                ->after('data_inicio');

            // Adicionar campo de valor customizado
            $table->decimal('valor', 10, 2)
                ->nullable()
                ->comment('Valor customizado para esta recorrência')
                ->after('data_fim');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contas_recorrentes', function (Blueprint $table) {
            // Reverter as alterações
            $table->renameColumn('intervalo', 'periodo_recorrencia');
            $table->renameColumn('data_inicio', 'data_proxima_recorrencia');
            $table->renameColumn('max_ocorrencias', 'recorrencias_restantes');

            $table->dropColumn([
                'tipo_recorrencia',
                'dia_mes',
                'dia_semana',
                'data_fim',
                'valor',
                'status'
            ]);
        });
    }
};
