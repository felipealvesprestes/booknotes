<?php

use App\Rules\FullName;
use Tests\TestCase;

uses(TestCase::class);

it('accepts values with at least two words', function (string $value): void {
    $rule = new FullName();
    $messages = [];

    $rule->validate('name', $value, function (string $message) use (&$messages): void {
        $messages[] = $message;
    });

    expect($messages)->toBeEmpty();
})->with([
    'simple name' => 'Maria Silva',
    'extra spaces' => ' João   Souza ',
    'compound last name' => 'Ana Clara de Lima',
]);

it('rejects values without a last name', function (string $value): void {
    $rule = new FullName();
    $messages = [];

    $rule->validate('name', $value, function (string $message) use (&$messages): void {
        $messages[] = $message;
    });

    expect($messages)->toBe([__('validation.full_name')]);
})->with([
    'single word' => 'Maria',
    'empty string' => '',
    'only spaces' => '   ',
]);
