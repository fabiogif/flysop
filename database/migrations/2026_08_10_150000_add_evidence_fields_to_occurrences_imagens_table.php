<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEvidenceFieldsToOccurrencesImagensTable extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::table('occurrences_imagens', function (Blueprint $table) {
            // Coluna 'url' nunca existiu nesta tabela apesar de OccurrencesImagens::$fillable e
            // OccurrenceService já gravarem nela — upload de anexo estava quebrado (erro de SQL
            // "column url does not exist" a cada tentativa). Corrigido junto com a Fase 3 porque
            // as evidências antes/depois dependem do upload funcionar.
            $table->string('url')->nullable()->after('occurrence_id');
            $table->unsignedBigInteger('uploaded_by_user_id')->nullable()->after('url');
            $table->string('phase', 10)->nullable()->after('uploaded_by_user_id'); // 'antes' | 'depois'
            $table->decimal('latitude', 9, 6)->nullable()->after('phase');
            $table->decimal('longitude', 9, 6)->nullable()->after('latitude');
            $table->timestamp('captured_at')->nullable()->after('longitude');

            $table->foreign('uploaded_by_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('occurrences_imagens', function (Blueprint $table) {
            $table->dropForeign(['uploaded_by_user_id']);
            $table->dropColumn(['url', 'uploaded_by_user_id', 'phase', 'latitude', 'longitude', 'captured_at']);
        });
    }
}
