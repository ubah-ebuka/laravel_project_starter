<?php

namespace App\Http\Middlewares\Customer;

use Closure;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Enums\RequestActionEnum;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PhoneNumberVerificationGuard
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

        if ($user->phone_verified_at === null) {
            return $this->failedResponse('Phone number not verified', 400, RequestActionEnum::PHONE_NOT_VERIFIED);
        }
        
        return $next($request);
    }
}
