<?php

namespace App\Http\Middlewares\Customer;

use App\Enums\RequestActionEnum;
use Closure;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EmailVerificationGuard
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

        if ($user->email_verified_at === null) {
            return $this->failedResponse('Email not verified', 400, RequestActionEnum::EMAIL_NOT_VERIFIED);
        }
        
        return $next($request);
    }
}
