<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Quem registra sem cadastro acompanha com protocolo + código de acompanhamento. O protocolo é
 * sequencial (adivinhável); o código é aleatório e só o hash fica no banco.
 */
class AddTrackingCodeHashToOccurrencesTable extends Migration
{
    public function up(): void
    {
        Schema::table('occurrences', function (Blueprint $table) {
            $table->string('tracking_code_hash', 64)->nullable()->after('is_anonymous');
        });
    }

    public function down(): void
    {
        Schema::table('occurrences', function (Blueprint $table) {
            $table->dropColumn('tracking_code_hash');
        });
    }
}
