<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Detecção de duplicidade (Fase 6): marca só uma sugestão para revisão humana — nunca
 * mescla/descarta automaticamente. Ver OccurrenceService::detectPossibleDuplicate().
 */
class AddPossibleDuplicateOfIdToOccurrencesTable extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::table('occurrences', function (Blueprint $table) {
            $table->unsignedBigInteger('possible_duplicate_of_id')->nullable()->after('driver_id');
            $table->foreign('possible_duplicate_of_id')->references('id')->on('occurrences')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('occurrences', function (Blueprint $table) {
            $table->dropForeign(['possible_duplicate_of_id']);
            $table->dropColumn('possible_duplicate_of_id');
        });
    }
}
