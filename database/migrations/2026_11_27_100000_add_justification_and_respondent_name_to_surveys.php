<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJustificationAndRespondentNameToSurveys extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::table('survey_questions', function (Blueprint $table) {
            $table->boolean('allow_justification')->default(false)->after('required');
        });

        Schema::table('survey_answers', function (Blueprint $table) {
            $table->text('justification')->nullable()->after('value');
        });

        Schema::table('survey_responses', function (Blueprint $table) {
            $table->string('respondent_name')->nullable()->after('survey_id');
        });
    }

    public function down(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropColumn('respondent_name');
        });

        Schema::table('survey_answers', function (Blueprint $table) {
            $table->dropColumn('justification');
        });

        Schema::table('survey_questions', function (Blueprint $table) {
            $table->dropColumn('allow_justification');
        });
    }
}
