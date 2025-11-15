<?php

namespace App\Http\Middlewares\Customer;

use App\Enums\RequestActionEnum;
use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (!$user || $user->type !== 'customer' || $user->status !== "active") {
            return $this->failedResponse('Unauthorized', 401, RequestActionEnum::NOT_AUTHENTICATED);
        }

        Auth::setUser($user);
        return $next($request);
    }
}
