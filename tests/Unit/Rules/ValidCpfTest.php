<?php

use App\Rules\ValidCpf;
use Tests\TestCase;

uses(TestCase::class);

it('accepts cpfs with punctuation and valid checksum', function (): void {
    $rule = new ValidCpf();
    $messages = [];

    $rule->validate('cpf', '111.444.777-35', function (string $message) use (&$messages): void {
        $messages[] = $message;
    });

    expect($messages)->toBeEmpty();
});

it('rejects cpfs with incorrect length or repeated digits', function (string $cpf): void {
    $rule = new ValidCpf();
    $messages = [];

    $rule->validate('cpf', $cpf, function (string $message) use (&$messages): void {
        $messages[] = $message;
    });

    expect($messages)->toBe([__('validation.invalid_cpf')]);
})->with([
    'too short' => '123.456.789-0',
    'repeated digits' => '111.111.111-11',
]);

it('rejects cpfs with invalid checksum even when digits count is correct', function (): void {
    $rule = new ValidCpf();
    $messages = [];

    $rule->validate('cpf', '111.444.777-36', function (string $message) use (&$messages): void {
        $messages[] = $message;
    });

    expect($messages)->toBe([__('validation.invalid_cpf')]);
});
