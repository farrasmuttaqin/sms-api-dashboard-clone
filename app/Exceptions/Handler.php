<?php

namespace Firstwap\SmsApiDashboard\Exceptions;

use Exception;
use Firstwap\SmsApiDashboard\Libraries\Report\NoDataToGenerateException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Handler extends ExceptionHandler
{

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        NoDataToGenerateException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  Request  $request
     * @param  \Exception  $exception
     * @return Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof AuthorizationException) {
            return $this->unauthorization($request, $exception);
        }

        if($exception instanceof NoDataToGenerateException){
            return $this->noDataToGenerate($request, $exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * Convert an authentication exception into a response.
     *
     * @param  Request  $request
     * @param  AuthenticationException  $exception
     * @return JsonResponse | RedirectResponse
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $request->expectsJson() ? response()->json(['errors' => [$exception->getMessage()]], 401) : redirect()->guest(route('auth.login'));
    }

    /**
     * Convert an AccessDeniedHttpException exception into a response.
     *
     * @param  Request  $request
     * @param  AuthorizationException  $exception
     * @return JsonResponse | RedirectResponse
     */
    protected function unauthorization($request, AuthorizationException $exception)
    {
        return $request->expectsJson() ? response()->json(['errors' => [$exception->getMessage()]], 403) : redirect(url('/'))
                        ->withErrors(['unauthorized' => $exception->getMessage()]);
    }

    /**
     * Convert an authentication exception into a response.
     *
     * @param  Request  $request
     * @param  AuthenticationException  $exception
     * @return JsonResponse | RedirectResponse
     */
    protected function noDataToGenerate($request, NoDataToGenerateException $exception)
    {
        return back()
                ->withInput()
                ->withErrors(['no_data' => $exception->getMessage()]);
    }
}
