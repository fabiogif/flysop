<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEnhancedAuthAndPublicProfileToClientsTable extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('username', 30)->nullable()->after('name');
            $table->string('avatar_url')->nullable()->after('username');
            $table->string('bio', 160)->nullable()->after('avatar_url');
            $table->string('website')->nullable()->after('bio');
            $table->string('google_id')->nullable()->after('website');
            $table->string('apple_id')->nullable()->after('google_id');
            // email_verified_at já existe desde create_clients_table
            $table->boolean('marketing_opt_in')->default(false)->after('apple_id');
            $table->timestamp('terms_accepted_at')->nullable()->after('marketing_opt_in');

            $table->unique(['tenant_id', 'username']);
            $table->unique(['google_id']);
            $table->unique(['apple_id']);
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique('clients_tenant_id_username_unique');
            $table->dropUnique('clients_google_id_unique');
            $table->dropUnique('clients_apple_id_unique');
            $table->dropColumn([
                'username',
                'avatar_url',
                'bio',
                'website',
                'google_id',
                'apple_id',
                'marketing_opt_in',
                'terms_accepted_at',
            ]);
        });
    }
}
