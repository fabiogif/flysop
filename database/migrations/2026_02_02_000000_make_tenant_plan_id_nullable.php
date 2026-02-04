<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Remove dependência de Planos: plan_id passa a ser opcional.
 * Funcionalidade de Planos foi removida do sistema.
 */
class MakeTenantPlanIdNullable extends Migration
{
    public $withinTransaction = false;

    public function up()
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
        });
        DB::statement('ALTER TABLE tenants ALTER COLUMN plan_id DROP NOT NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE tenants ALTER COLUMN plan_id SET NOT NULL');
        Schema::table('tenants', function (Blueprint $table) {
            $table->foreign('plan_id')->references('id')->on('plans');
        });
    }
}
