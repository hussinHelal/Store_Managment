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
     * @var array<int, class-string<\Throwable>>
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

    public function register()
    {
        // Keep default reporting
    }

    public function render($request, Throwable $e)
    {
        $status = 500;
        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();
        }

        // For AJAX/JSON requests return structured JSON
        if ($request->expectsJson() || $request->ajax() || str_contains($request->header('Accept', ''), 'application/json')) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage() ?: ($status === 404 ? 'Not Found' : 'Server Error'),
            ], $status);
        }

        // For regular requests, try to show a friendly error page if available
        $view = "errors.{$status}";
        if (view()->exists($view)) {
            return response()->view($view, ['exception' => $e], $status);
        }

        return parent::render($request, $e);
    }
}
