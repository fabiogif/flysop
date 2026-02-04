<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOccurrencesTable extends Migration
{
    public $withinTransaction = false;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('occurrences', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('description')->nullable();
            $table->string('name')->nullable();
            $table->string('cpf')->nullable();
            $table->string('rg')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();

            $table->unsignedBigInteger('users_id');
            $table->unsignedBigInteger('clients_id');
            $table->unsignedBigInteger('issuings_id');
            $table->unsignedBigInteger('type_occurrences_id');
            $table->unsignedBigInteger('status_occurrences_id');

            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();

            $table->foreign('users_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('clients_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('issuings_id')->references('id')->on('issuings')->onDelete('cascade');
            $table->foreign('type_occurrences_id')->references('id')->on('type_occurrences')->onDelete('cascade');
            $table->foreign('status_occurrences_id')->references('id')->on('status_occurrences')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('occurrences');
    }
}