<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Corrige bug pré-existente: o formulário público de ocorrências (Api\OccurrenceApiController
 * @createNewOccurrence, sem autenticação por decisão de produto) nunca preencheu "users_id",
 * mas a coluna era NOT NULL — toda submissão de cidadão sem login derrubava com erro 500.
 * "users_id" registra quem cadastrou pelo painel admin; para ocorrências públicas não há
 * usuário interno associado, então a coluna passa a aceitar null (sem doctrine/dbal
 * disponível no projeto, ajuste via SQL bruto em vez de Schema::table(...)->change()).
 */
return new class extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE occurrences ALTER COLUMN users_id DROP NOT NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE occurrences ALTER COLUMN users_id SET NOT NULL');
    }
};
