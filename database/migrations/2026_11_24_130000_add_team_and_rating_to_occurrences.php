<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Eixo 4 — Fase C (aditiva, tudo nulo): equipe por secretaria (teams.issuing_id), equipe
 * responsável pela ocorrência (occurrences.team_id) e avaliação do cidadão (nota 1–5).
 */
return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->unsignedBigInteger('issuing_id')->nullable()->after('department_id');
            $table->foreign('issuing_id')->references('id')->on('issuings')->onDelete('set null');
        });

        Schema::table('occurrences', function (Blueprint $table) {
            $table->unsignedBigInteger('team_id')->nullable()->after('driver_id');
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('set null');
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('rating_comment')->nullable();
            $table->timestamp('rated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('occurrences', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropColumn(['team_id', 'rating', 'rating_comment', 'rated_at']);
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropForeign(['issuing_id']);
            $table->dropColumn('issuing_id');
        });
    }
};
