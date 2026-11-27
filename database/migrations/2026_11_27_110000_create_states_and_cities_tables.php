<?php

use Database\Seeders\BrazilianLocationsSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Estados e municípios (IBGE) — fonte: github.com/kelvins/municipios-brasileiros (CSV em
 * database/data/ibge). O id é o próprio código IBGE. O seed roda aqui porque o deploy
 * (fly.toml release_command) só executa `migrate`, nunca `db:seed`.
 */
class CreateStatesAndCitiesTables extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('states', function (Blueprint $table) {
            $table->unsignedSmallInteger('id')->primary();
            $table->char('uf', 2)->unique();
            $table->string('name');
            $table->string('region', 20);
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->unsignedSmallInteger('state_id');
            $table->string('name');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('capital')->default(false);

            $table->foreign('state_id')->references('id')->on('states')->onDelete('cascade');
            $table->index(['state_id', 'name']);
        });

        (new BrazilianLocationsSeeder())->run();
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
        Schema::dropIfExists('states');
    }
}
