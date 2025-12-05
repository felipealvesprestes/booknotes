<?php

namespace Database\Factories;

use App\Models\Discipline;
use App\Models\Note;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Note>
 */
class NoteFactory extends Factory
{
    protected $model = Note::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'content' => $this->faker->paragraph(),
            'flashcard_question' => $this->faker->sentence(),
            'flashcard_answer' => $this->faker->sentence(),
            'is_flashcard' => true,
            'discipline_id' => Discipline::factory(),
        ];
    }
}
