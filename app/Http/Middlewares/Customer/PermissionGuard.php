<?php

namespace App\Http\Middlewares\Customer;

use App\Enums\PermissionEnum;
use App\Enums\RequestActionEnum;
use App\Models\Permission;
use App\Services\UserService;
use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PermissionGuard
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $userService = (new UserService());
        
        if (!$userService->hasPermission($permission)) {
            return $this->failedResponse('Unauthorized', 403, RequestActionEnum::NOT_AUTHORIZED);
        }
        
        return $next($request);
    }
}
