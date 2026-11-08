<?php

use App\Http\Middleware\NormalizeFormCsrf;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Responses\FormRequestResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->prependToGroup('web', NormalizeFormCsrf::class);
        $middleware->prependToGroup('web', SecurityHeaders::class);
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('admin', 'admin/*')
            ? route('admin.login')
            : route('admin.login'));

        // Exclude Stripe webhook from CSRF verification
        $middleware->validateCsrfTokens(except: [
            'api/webhooks/stripe',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || FormRequestResponse::wantsJson($request),
        );

        $exceptions->render(function (ValidationException $exception, Request $request) {
            return FormRequestResponse::handleException($exception, $request);
        });

        $exceptions->render(function (TokenMismatchException $exception, Request $request) {
            return FormRequestResponse::handleException($exception, $request);
        });

        $exceptions->render(function (HttpException $exception, Request $request) {
            if ($exception->getStatusCode() === 419 && FormRequestResponse::wantsJson($request)) {
                return FormRequestResponse::csrfExpired();
            }

            return null;
        });
    })->create();
