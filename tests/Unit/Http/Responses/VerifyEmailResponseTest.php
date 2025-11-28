<?php

use App\Http\Responses\VerifyEmailResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use Tests\TestCase;

uses(TestCase::class);

test('verify email response redirects to configured path', function (): void {
    config(['fortify.redirects.email-verification' => '/dashboard']);

    $request = Request::create('/email/verify', 'GET');

    $response = (new VerifyEmailResponse())->toResponse($request);

    expect($response->getTargetUrl())->toBe(url('/dashboard?verified=1'));
});
