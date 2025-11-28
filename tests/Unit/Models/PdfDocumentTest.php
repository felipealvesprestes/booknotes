<?php

use App\Models\PdfDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Number;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('computes readable size and respects casts', function (): void {
    $user = User::factory()->create();

    $document = new PdfDocument([
        'title' => 'Summary',
        'original_name' => 'summary.pdf',
        'path' => 'pdfs/summary.pdf',
        'size' => 4_096,
        'last_opened_at' => Date::now(),
    ]);
    $document->user()->associate($user);
    $document->save();

    expect($document->readable_size)->toBe(Number::fileSize(4_096))
        ->and($document->last_opened_at)->toBeInstanceOf(\DateTimeInterface::class);
});
