<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Illuminate\Http\Request;

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

    /**
     * Render an exception into an HTTP response.
     *
     * Ensure that throttling (429) errors return JSON for API requests
     * instead of the default HTML page.
     */
    public function render($request, Throwable $exception)
    {
        if (($request instanceof Request && $request->is('api/*')) || $request->expectsJson()) {
            if ($exception instanceof ThrottleRequestsException || $exception instanceof TooManyRequestsHttpException) {
                $message = $exception->getMessage() ?: 'Too Many Requests.';
                $headers = method_exists($exception, 'getHeaders') ? $exception->getHeaders() : [];
                return response()->json([
                    'message' => $message,
                ], 429, $headers);
            }
        }

        return parent::render($request, $exception);
    }
}
