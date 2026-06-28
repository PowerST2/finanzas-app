<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class EnsureRegistrationIsAllowed
{
    public function handle(Request $request, Closure $next)
    {
        abort_if(! config('app.allow_registration') && User::exists(), 403);

        return $next($request);
    }
}
