<?php

use App\Models\Discipline;
use App\Models\Notebook;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('returns disciplines belonging to the notebook', function (): void {
    $user = User::factory()->create();

    $notebook = new Notebook([
        'title' => 'Sciences',
        'description' => 'STEM notes',
    ]);
    $notebook->user()->associate($user);
    $notebook->save();

    $disciplineA = new Discipline([
        'title' => 'Physics',
        'description' => null,
        'notebook_id' => $notebook->id,
    ]);
    $disciplineA->user()->associate($user);
    $disciplineA->save();

    $disciplineB = new Discipline([
        'title' => 'Chemistry',
        'description' => null,
        'notebook_id' => $notebook->id,
    ]);
    $disciplineB->user()->associate($user);
    $disciplineB->save();

    expect($notebook->disciplines)->toHaveCount(2)
        ->and($notebook->disciplines->pluck('title')->all())->toBe(['Physics', 'Chemistry']);
});
