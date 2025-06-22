<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (HttpException $e) {
            if ($e->getStatusCode() === 403) {
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'message' => 'Unauthorized action.',
                        'error' => 'You do not have permission to access this resource.'
                    ], 403);
                }
                
                return redirect()->route('dashboard')->with('error', 'You do not have permission to access this page.');
            }
        });
    }
} 