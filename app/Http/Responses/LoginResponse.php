<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Fortify;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $redirect = redirect()->intended(Fortify::redirects('login'))->getTargetUrl();

        return $request->wantsJson()
            ? response()->json([
                'two_factor' => false,
                'redirect' => $redirect,
            ])
            : redirect()->to($redirect);
    }
}
