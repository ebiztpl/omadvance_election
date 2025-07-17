<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    // public function render($request, Throwable $exception)
    // {
    //     // Optional: force Laravel to use your views even if APP_DEBUG=true
    //     config(['app.debug' => false]);

    //     // Handle JSON/API requests separately
    //     if ($request->expectsJson()) {
    //         $statusCode = $exception instanceof HttpExceptionInterface
    //             ? $exception->getStatusCode()
    //             : 500;

    //         return response()->json([
    //             'error' => 'Something went wrong.',
    //             'message' => config('app.debug') ? $exception->getMessage() : 'Server Error',
    //         ], $statusCode);
    //     }

    //     // Determine status code
    //     $statusCode = $exception instanceof HttpExceptionInterface
    //         ? $exception->getStatusCode()
    //         : 500;

    //     // If there's a custom view for this status code, show it
    //     if (view()->exists("errors.{$statusCode}")) {
    //         return response()->view("errors.{$statusCode}", ['exception' => $exception], $statusCode);
    //     }

    //     // Default fallback
    //     return response()->view("errors.500", ['exception' => $exception], 500);
    // }
}
