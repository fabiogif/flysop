<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDriverIdToOccurrencesTable extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::table('occurrences', function (Blueprint $table) {
            $table->unsignedBigInteger('driver_id')->nullable()->after('status_occurrences_id');
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('occurrences', function (Blueprint $table) {
            $table->dropForeign(['driver_id']);
            $table->dropColumn('driver_id');
        });
    }
}
