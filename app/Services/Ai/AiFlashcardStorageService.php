<?php

namespace App\Services\Ai;

use App\Models\Discipline;
use App\Models\Log;
use App\Models\Note;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AiFlashcardStorageService
{
    /**
     * @param  array<int, array{question: string, answer: string, extra?: string|null}>  $flashcards
     */
    public function store(Discipline $discipline, array $flashcards, string $source = 'ai_flashcards'): int
    {
        $disciplineId = $discipline->getKey();
        $count = 0;

        DB::transaction(function () use ($flashcards, $disciplineId, $source, &$count): void {
            foreach ($flashcards as $card) {
                $question = $this->sanitizeText($card['question'] ?? '');
                $answer = $this->sanitizeText($card['answer'] ?? '');
                $extra = isset($card['extra']) ? $this->sanitizeText($card['extra']) : null;

                if ($question === '' || $answer === '') {
                    continue;
                }

                $finalAnswer = $this->formatAnswerWithExtra($answer, $extra);

                $note = Note::create([
                    'title' => Str::of($question)->limit(255, '')->value(),
                    'content' => $finalAnswer,
                    'is_flashcard' => true,
                    'flashcard_question' => Str::of($question)->limit(255, '')->value(),
                    'flashcard_answer' => $finalAnswer,
                    'discipline_id' => $disciplineId,
                ]);

                Log::create([
                    'action' => 'note.created',
                    'context' => [
                        'note_id' => $note->id,
                        'discipline_id' => $note->discipline_id,
                        'is_flashcard' => true,
                        'source' => $source,
                    ],
                ]);

                $count++;
            }
        });

        return $count;
    }

    protected function sanitizeText(?string $value): string
    {
        $plain = trim(strip_tags((string) $value));

        return preg_replace('/\\s+/u', ' ', $plain) ?? '';
    }

    protected function formatAnswerWithExtra(string $answer, ?string $extra): string
    {
        if (blank($extra)) {
            return $answer;
        }

        return "{$answer}\n\n" . __('ai_flashcards.extra_note', ['extra' => $extra]);
    }
}
