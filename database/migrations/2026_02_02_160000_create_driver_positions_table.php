<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriverPositionsTable extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('driver_positions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id');
            $table->unsignedBigInteger('occurrence_id')->nullable();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->float('accuracy')->nullable();
            $table->timestamps();

            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->foreign('occurrence_id')->references('id')->on('occurrences')->onDelete('set null');
            $table->index(['driver_id', 'created_at']);
            $table->index(['occurrence_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_positions');
    }
}
