<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddProtocolPriorityAndAddressFieldsToOccurrencesTable extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::table('occurrences', function (Blueprint $table) {
            $table->string('protocol')->nullable()->after('id');
            $table->unsignedBigInteger('priority_id')->nullable()->after('status_occurrences_id');
            $table->timestamp('due_at')->nullable()->after('priority_id');
            $table->string('neighborhood')->nullable()->after('address');
            $table->string('city')->nullable()->after('neighborhood');
            $table->string('state')->nullable()->after('city');
            $table->string('zip')->nullable()->after('state');

            $table->foreign('priority_id')->references('id')->on('priorities')->onDelete('set null');
        });

        $this->backfillProtocol();

        Schema::table('occurrences', function (Blueprint $table) {
            $table->unique('protocol');
        });
    }

    /**
     * Gera protocolo OC-{ano}-{sequencial} para ocorrências já existentes,
     * agrupando por ano de created_at. Ocorrências novas recebem o protocolo
     * na criação via OccurrenceService — este backfill cobre apenas o histórico.
     */
    private function backfillProtocol(): void
    {
        $rows = DB::table('occurrences')->orderBy('created_at')->orderBy('id')->get(['id', 'created_at']);

        $sequenceByYear = [];
        foreach ($rows as $row) {
            $year = $row->created_at ? date('Y', strtotime($row->created_at)) : date('Y');
            $sequenceByYear[$year] = ($sequenceByYear[$year] ?? 0) + 1;
            $protocol = sprintf('OC-%s-%05d', $year, $sequenceByYear[$year]);

            DB::table('occurrences')->where('id', $row->id)->update(['protocol' => $protocol]);
        }
    }

    public function down(): void
    {
        Schema::table('occurrences', function (Blueprint $table) {
            $table->dropForeign(['priority_id']);
            $table->dropUnique(['protocol']);
            $table->dropColumn(['protocol', 'priority_id', 'due_at', 'neighborhood', 'city', 'state', 'zip']);
        });
    }
}
