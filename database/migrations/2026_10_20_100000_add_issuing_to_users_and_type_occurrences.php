<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Acesso do gestor por órgão: users.issuing_id marca o gestor do órgão (nulo = não é gestor);
 * type_occurrences.issuing_id define o órgão responsável pelo tipo (roteamento da ocorrência
 * aberta pelo app); users.notify_new_occurrences é a preferência do feed de notificações.
 */
class AddIssuingToUsersAndTypeOccurrences extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('issuing_id')->nullable()->after('tenant_id');
            $table->boolean('notify_new_occurrences')->default(true)->after('issuing_id');
            $table->foreign('issuing_id')->references('id')->on('issuings')->onDelete('set null');
        });

        Schema::table('type_occurrences', function (Blueprint $table) {
            $table->unsignedBigInteger('issuing_id')->nullable()->after('parent_id');
            $table->foreign('issuing_id')->references('id')->on('issuings')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('type_occurrences', function (Blueprint $table) {
            $table->dropForeign(['issuing_id']);
            $table->dropColumn('issuing_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['issuing_id']);
            $table->dropColumn(['issuing_id', 'notify_new_occurrences']);
        });
    }
}
