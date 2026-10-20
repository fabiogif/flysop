<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ocorrência anônima: a identidade do autor fica oculta para o órgão (gestor/painel), mas a
 * ocorrência continua ligada à conta do cidadão logado para ele acompanhar.
 */
class AddIsAnonymousToOccurrencesTable extends Migration
{
    public function up(): void
    {
        Schema::table('occurrences', function (Blueprint $table) {
            $table->boolean('is_anonymous')->default(false)->after('clients_id');
        });
    }

    public function down(): void
    {
        Schema::table('occurrences', function (Blueprint $table) {
            $table->dropColumn('is_anonymous');
        });
    }
}
