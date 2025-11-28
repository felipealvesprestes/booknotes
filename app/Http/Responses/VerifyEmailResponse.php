<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\VerifyEmailResponse as VerifyEmailResponseContract;
use Laravel\Fortify\Fortify;

class VerifyEmailResponse implements VerifyEmailResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     */
    public function toResponse($request)
    {
        $verificationRedirect = Fortify::redirects('email-verification').'?verified=1';

        return $request->wantsJson() ? new JsonResponse('', 204) : redirect()->to($verificationRedirect);
    }
}
