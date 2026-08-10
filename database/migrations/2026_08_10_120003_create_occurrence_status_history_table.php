<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOccurrenceStatusHistoryTable extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('occurrence_status_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('occurrence_id');
            $table->unsignedBigInteger('from_status_id')->nullable();
            $table->unsignedBigInteger('to_status_id');
            $table->unsignedBigInteger('changed_by_user_id')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('occurrence_id')->references('id')->on('occurrences')->onDelete('cascade');
            $table->foreign('from_status_id')->references('id')->on('status_occurrences')->onDelete('set null');
            $table->foreign('to_status_id')->references('id')->on('status_occurrences')->onDelete('cascade');
            $table->foreign('changed_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['occurrence_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('occurrence_status_history');
    }
}
