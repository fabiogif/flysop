<?php

namespace Database\Factories;

use App\Models\Survey;
use App\Models\SurveyQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

class SurveyQuestionFactory extends Factory
{
    protected $model = SurveyQuestion::class;

    public function definition(): array
    {
        return [
            'survey_id' => Survey::factory(),
            'type' => SurveyQuestion::TYPE_TEXT,
            'prompt' => $this->faker->sentence(6),
            'options' => null,
            'required' => true,
            'sort_order' => 0,
        ];
    }

    public function singleChoice(array $options = ['Sim', 'Não', 'Talvez']): self
    {
        return $this->state(fn () => [
            'type' => SurveyQuestion::TYPE_SINGLE_CHOICE,
            'options' => $options,
        ]);
    }

    public function scale(): self
    {
        return $this->state(fn () => [
            'type' => SurveyQuestion::TYPE_SCALE,
            'options' => null,
        ]);
    }
}
