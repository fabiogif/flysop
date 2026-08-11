<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Relatórios exportáveis (Fase 6): gerados via fila (worker da Fase 0), não na request.
 * O usuário é notificado (App\Notifications\ReportGeneratedNotification) quando o arquivo
 * fica pronto para download.
 */
class CreateReportsTable extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type', 40); // occurrences | status_durations
            $table->string('status', 20)->default('pending'); // pending | ready | failed
            $table->json('filters')->nullable();
            $table->string('file_path')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
}
