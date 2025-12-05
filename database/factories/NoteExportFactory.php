<?php

namespace Database\Factories;

use App\Models\NoteExport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\NoteExport>
 */
class NoteExportFactory extends Factory
{
    protected $model = NoteExport::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'file_name' => 'notes-' . $this->faker->uuid . '.zip',
            'file_path' => 'exports/' . $this->faker->sha1 . '.zip',
            'note_count' => $this->faker->numberBetween(1, 10),
            'flashcard_count' => $this->faker->numberBetween(0, 10),
            'status' => NoteExport::STATUS_PENDING,
            'filters' => ['discipline' => $this->faker->word],
            'failure_reason' => null,
            'finished_at' => null,
        ];
    }

    public function completed(string $path = null): self
    {
        return $this->state(fn () => [
            'status' => NoteExport::STATUS_COMPLETED,
            'file_path' => $path ?? 'exports/' . $this->faker->sha1 . '.zip',
            'finished_at' => now(),
        ]);
    }

    public function processing(): self
    {
        return $this->state(fn () => [
            'status' => NoteExport::STATUS_PROCESSING,
            'file_path' => null,
            'finished_at' => null,
        ]);
    }
}
