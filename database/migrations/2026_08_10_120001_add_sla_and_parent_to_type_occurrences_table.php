<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSlaAndParentToTypeOccurrencesTable extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::table('type_occurrences', function (Blueprint $table) {
            $table->unsignedInteger('sla_hours')->nullable()->after('name');
            $table->unsignedBigInteger('parent_id')->nullable()->after('sla_hours');
            $table->foreign('parent_id')->references('id')->on('type_occurrences')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('type_occurrences', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'sla_hours']);
        });
    }
}
