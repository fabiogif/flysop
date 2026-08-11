<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivityLogTable extends Migration
{
    /**
     * Producao (Fly.io) conecta via um pooler local (PgCat/PgBouncer, 127.0.0.1:5432) que,
     * em modo transaction-pooling, atribui o erro real de uma DDL ao statement SEGUINTE dentro
     * da mesma transacao (ex.: "create index subject" falhando com "transaction aborted" quando
     * o CREATE TABLE anterior foi o que realmente falhou) — mesma SQL roda sem problema no
     * Postgres direto do docker-compose.dev.yml. Desabilitar a transacao implicita da migration
     * e o contorno documentado do Laravel para esse tipo de ambiente.
     */
    public $withinTransaction = false;

    public function up()
    {
        Schema::connection(config('activitylog.database_connection'))->create(config('activitylog.table_name'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->nullableMorphs('subject', 'subject');
            $table->nullableMorphs('causer', 'causer');
            $table->json('properties')->nullable();
            $table->timestamps();
            $table->index('log_name');
        });
    }

    public function down()
    {
        Schema::connection(config('activitylog.database_connection'))->dropIfExists(config('activitylog.table_name'));
    }
}
