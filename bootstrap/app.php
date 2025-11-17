<?php

use App\Traits\ApiResponse;
use App\Enums\RequestActionEnum;
use App\Http\Middlewares\Customer\PermissionGuard as CustomerPermissionGuard;
use App\Http\Middlewares\Admin\PermissionGuard as AdminPermissionGuard;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middlewares\Customer\EmailVerificationGuard;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Http\Middlewares\Customer\Authenticate as AuthenticateCustomer;
use App\Http\Middlewares\Admin\Authenticate as AuthenticateAdmin;
use App\Http\Middlewares\Customer\PhoneNumberVerificationGuard;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->prefix('web-api')
                ->name('web-api.')
                ->group(base_path('routes/web-api.php'));
            
            Route::middleware('web')
                ->prefix('panel-api')
                ->name('panel-api.')
                ->group(base_path('routes/panel-api.php'));

            Route::middleware('api')
                ->prefix('mobile-api')
                ->name('mobile-api.')
                ->group(base_path('routes/mobile-api.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'customer-auth' => AuthenticateCustomer::class,
            'email-verified' => EmailVerificationGuard::class,
            'phone-verified' => PhoneNumberVerificationGuard::class,
            'customer-has' => CustomerPermissionGuard::class,
            'admin-has' => AdminPermissionGuard::class,
            'admin-auth' => AuthenticateAdmin::class,
        ]);

        $middleware->group('web-api', [
            EnsureFrontendRequestsAreStateful::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $trait = new class { use ApiResponse; };
        
        $exceptions->render(function (ValidationException $e, $request) use ($trait) {
            if ($request->expectsJson()) {
                return $trait->failedResponse(
                    message: 'Validation failed',
                    statusCode: 422,
                    responseCode: RequestActionEnum::VALIDATION_ERROR,
                    errors: $e->validator->errors()
                );
            }
        });

        $exceptions->render(function (AuthenticationException $e, $request) use ($trait) {
            if ($request->expectsJson()) {
                return $trait->failedResponse(
                    message: 'Unauthenticated.',
                    statusCode: 401,
                    responseCode: RequestActionEnum::NOT_AUTHENTICATED
                );
            }
        });

        $exceptions->render(function (HttpException $e, $request) use ($trait) {
            if ($request->expectsJson()) {
                $statusCode = $e->getStatusCode();

                $messages = [
                    401 => "Not authenticated",
                    403 => "Not authorized",
                    404 => "Resource not found",
                    422 => "Request error",
                    419 => "CSRF token mismatch"
                ];

                return $trait->failedResponse(
                    message: $messages[$statusCode] ?? 'Something went wrong',
                    statusCode: $statusCode,
                    responseCode: match($statusCode) {
                        401 => RequestActionEnum::NOT_AUTHENTICATED,
                        403 => RequestActionEnum::NOT_AUTHORIZED,
                        404 => RequestActionEnum::RESOURCE_NOT_FOUND,
                        422 => RequestActionEnum::REQUEST_ERROR,
                        419 => RequestActionEnum::CSRF_TOKEN_MISMATCH,
                        default => RequestActionEnum::SERVER_ERROR,
                    }
                );
            }
        });

        $exceptions->render(function (Throwable $e, $request) use ($trait) {
            if ($request->expectsJson()) {
                Log::error('Unhandled exception', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return $trait->errorResponse(
                    message: 'Internal server error',
                    statusCode: 500,
                    responseCode: RequestActionEnum::SERVER_ERROR
                );
            }
        });
    })->create();
