<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Mitoop\Http\Responder;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: [
            __DIR__.'/../routes/admin.php',
            __DIR__.'/../routes/api.php',
        ],
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: [
            'admin9_token',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(static function (Throwable $e, Request $request) {
            if ($request->expectsJson()) {
                return match (true) {
                    $e instanceof AuthenticationException, $e instanceof JWTException => app(Responder::class)->reject('未认证或身份已失效，请重新登录'),
                    $e instanceof NotFoundHttpException => app(Responder::class)->error('请求的资源不存在'),
                    $e instanceof ValidationException => app(Responder::class)->error(Arr::first(Arr::flatten($e->errors()))),
                    $e instanceof ThrottleRequestsException => app(Responder::class)->error('请求过于频繁，请稍后重试'),
                    default => app(Responder::class)->error(
                        $e->getMessage() ?: '系统异常，请稍后重试',
                        data: is_local() ? format_exception($e) : null
                    ),
                };
            }

            return null;
        });
    })->create();
