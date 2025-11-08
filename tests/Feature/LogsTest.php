<?php

use App\Models\Log;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('preenche automaticamente o user_id ao criar um log', function () {
    $user = User::factory()->create();

    actingAs($user);

    $log = Log::create([
        'action' => 'user.login',
        'context' => ['ip' => '127.0.0.1'],
    ]);

    expect($log->user_id)->toBe($user->id);
    expect(Log::withoutGlobalScopes()->first()->user_id)->toBe($user->id);
});

it('filtra consultas pelo usuário autenticado', function () {
    [$firstUser, $secondUser] = User::factory()->count(2)->create();

    actingAs($firstUser);
    Log::create(['action' => 'user.login']);

    actingAs($secondUser);
    Log::create(['action' => 'user.logout']);

    actingAs($firstUser);
    expect(Log::pluck('action'))->toMatchArray(['user.login']);

    actingAs($secondUser);
    expect(Log::pluck('action'))->toMatchArray(['user.logout']);

    expect(Log::withoutGlobalScopes()->count())->toBe(2);
});
