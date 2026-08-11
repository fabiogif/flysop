<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Especialidade da equipe (Fase 5 — despacho inteligente): quando setado, a equipe só
 * entra no ranking de sugestão para ocorrências desse tipo. Nulo = atende qualquer tipo.
 */
class AddTypeOccurrencesIdToTeamsTable extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->unsignedBigInteger('type_occurrences_id')->nullable()->after('department_id');
            $table->foreign('type_occurrences_id')->references('id')->on('type_occurrences')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropForeign(['type_occurrences_id']);
            $table->dropColumn('type_occurrences_id');
        });
    }
}
