<?php

namespace Database\Factories;

use App\Models\PdfDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\PdfDocument>
 */
class PdfDocumentFactory extends Factory
{
    protected $model = PdfDocument::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'original_name' => $this->faker->lexify('document-????.pdf'),
            'path' => 'pdfs/'.$this->faker->uuid.'.pdf',
            'size' => $this->faker->numberBetween(10_000, 500_000),
            'last_opened_at' => now(),
        ];
    }
}
