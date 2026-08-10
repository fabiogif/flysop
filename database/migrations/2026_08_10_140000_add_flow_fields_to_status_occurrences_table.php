<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFlowFieldsToStatusOccurrencesTable extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::table('status_occurrences', function (Blueprint $table) {
            $table->boolean('is_terminal')->default(false)->after('name');
            $table->unsignedInteger('sort_order')->default(0)->after('is_terminal');
        });
    }

    public function down(): void
    {
        Schema::table('status_occurrences', function (Blueprint $table) {
            $table->dropColumn(['is_terminal', 'sort_order']);
        });
    }
}
