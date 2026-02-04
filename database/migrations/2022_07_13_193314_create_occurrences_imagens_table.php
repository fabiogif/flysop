<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOccurrencesImagensTable extends Migration
{
    public $withinTransaction = false;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('occurrences_imagens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('occurrence_id');
            $table->foreign('occurrence_id')->references('id')->on('occurrences')->onDelete('cascade');
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
        Schema::dropIfExists('occurrences_imagens');
    }
}
