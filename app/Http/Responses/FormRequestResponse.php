<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;

class FormRequestResponse
{
    public static function wantsJson(Request $request): bool
    {
        return $request->expectsJson()
            || $request->ajax()
            || $request->is('contact-us')
            || $request->is('*/contact-us')
            || $request->is('booking/ajax')
            || $request->is('*/booking/ajax');
    }

    public static function validation(ValidationException $exception): \Illuminate\Http\JsonResponse
    {
        $message = collect($exception->errors())->flatten()->first() ?? $exception->getMessage();

        return response()->json([
            'success' => false,
            'data' => $message,
            'errors' => $exception->errors(),
        ], $exception->status);
    }

    public static function csrfExpired(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => 'Your session has expired. Please refresh the page and try again.',
        ], 419);
    }

    public static function handleException(\Throwable $exception, Request $request): ?\Illuminate\Http\JsonResponse
    {
        if (!static::wantsJson($request)) {
            return null;
        }

        if ($exception instanceof ValidationException) {
            return static::validation($exception);
        }

        if ($exception instanceof TokenMismatchException) {
            return static::csrfExpired();
        }

        return null;
    }
}