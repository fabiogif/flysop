<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddPhoneAndRelaxAuthToClientsTable extends Migration
{
    public $withinTransaction = false;

    /**
     * Login leve do cidadão (app/site público, sem senha): nome + celular obrigatórios,
     * e-mail opcional. "clients" antes exigia email único + password (auth tradicional do
     * painel) — agora também recebe o cadastro do cidadão, então os dois viram opcionais
     * e "phone" (novo) é o identificador único por tenant desse fluxo.
     *
     * ALTER COLUMN ... DROP NOT NULL via SQL puro (não Blueprint::change()) porque o
     * projeto não tem doctrine/dbal instalado (exigido pelo Laravel 8 para ->change()) —
     * mesma escolha já usada em outras migrations Postgres-specific deste projeto.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
        });

        DB::statement('ALTER TABLE clients ALTER COLUMN email DROP NOT NULL');
        DB::statement('ALTER TABLE clients ALTER COLUMN password DROP NOT NULL');

        Schema::table('clients', function (Blueprint $table) {
            $table->unique(['tenant_id', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique(['clients_tenant_id_phone_unique']);
            $table->dropColumn('phone');
        });

        DB::statement('ALTER TABLE clients ALTER COLUMN email SET NOT NULL');
        DB::statement('ALTER TABLE clients ALTER COLUMN password SET NOT NULL');
    }
}
