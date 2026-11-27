<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Uma resposta por respondente: HMAC de quem respondeu (cidadão logado, aparelho ou cookie
 * do navegador) — deduplica sem guardar a identidade. NULL (respostas antigas) não conflita.
 */
class AddRespondentKeyToSurveyResponses extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->string('respondent_key', 64)->nullable()->after('respondent_name');
            $table->unique(['survey_id', 'respondent_key'], 'survey_responses_survey_respondent_unique');
        });
    }

    public function down(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropUnique('survey_responses_survey_respondent_unique');
            $table->dropColumn('respondent_key');
        });
    }
}
