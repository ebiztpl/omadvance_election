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
    //     // Handle JSON requests (like AJAX/API)
    //     if ($request->expectsJson()) {
    //         $statusCode = $exception instanceof HttpExceptionInterface
    //             ? $exception->getStatusCode()
    //             : 500;

    //         return response()->json([
    //             'error' => true,
    //             'message' => config('app.debug') ? $exception->getMessage() : 'Server Error',
    //         ], $statusCode);
    //     }

    //     // Handle normal web (HTML) requests
    //     if ($exception instanceof HttpExceptionInterface) {
    //         $statusCode = $exception->getStatusCode();

    //         if (view()->exists("errors.{$statusCode}")) {
    //             return response()->view("errors.{$statusCode}", ['exception' => $exception], $statusCode);
    //         }
    //     }

    //     // Fallback to 500 error view
    //     return response()->view("errors.500", ['exception' => $exception], 500);
    // }
}
