<?php

use App\Models\User;
use App\Models\Notebook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('keeps scope untouched when no user is authenticated', function (): void {
    Auth::logout();

    $builder = Notebook::query()->ownedBy(null);

    expect($builder->getQuery()->wheres)->toBeEmpty();
});
