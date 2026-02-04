<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdToDriversTable extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('tenant_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
    }
}
