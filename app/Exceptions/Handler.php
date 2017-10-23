<?php

namespace App\Exceptions;

use App\Models\User;
use App\Http\Controllers\APIController;
use App\Helpers\Arr;
use App\Helpers\Logger;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Validation\UnauthorizedException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use League\OAuth2\Server\Exception\OAuthException;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException as SymfonyMethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException as SymfonyNotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        $api = new APIController($request);

        if($e instanceof OAuthException || $e->getMessage() == 'invalid_credentials')
        {
            $data = [
                'error' => isset($e->errorType) ? $e->errorType : null,
                'show_captcha' => User::$_DONT_SHOW_CAPTCHA,
                'error_description' => $e->getMessage(),
            ];

            if($e->getMessage() == 'invalid_credentials')
            {
                $data['show_captcha'] = User::$_SHOW_CAPTCHA;
            }

            //Clean HTML
            $data = Arr::getPurifiedDataArray($data);

            /**
             * Return custom error message
             */
            return $api->respondAuthorizationError($data, trans('validation.login.invalid_credentials'));
        }

        if($e instanceof UnauthorizedException)
        {
            // We dont log unauthorized access, else logfile will be populated fast
            return $api->respondAuthorizationError();
        }

        if ($e instanceof NotFoundHttpException
            || $e instanceof ModelNotFoundException)
        {
            self::logger($e);
            return $api->respondNotFound();
        }

        if($e instanceof MethodNotAllowedHttpException
            || $e instanceof SymfonyNotFoundHttpException
            || $e instanceof SymfonyMethodNotAllowedHttpException)
        {
            self::logger($e);
            return $api->respondMethodNotAllowed();
        }

        if($e instanceof FatalErrorException)
        {
            self::logger($e);
            return $api->respondInternalError(method_exists($e, 'getMessage') ? $e->getMessage() : null);
        }

        if($e instanceof BadRequestHttpException)
        {
            self::logger($e);
            return $api->respondValidationError($e->getMessage());
        }

        if ($e instanceof Exception)
        {
            self::logger($e);
            return $api->respondInternalError(method_exists($e, 'getMessage') ? $e->getMessage() : null);
        }



        return parent::render($request, $e);
    }

    /**
     * Call logger to generate log file
     *
     * @param $exception
     */
    public static function logger($exception)
    {
        Logger::create(Route::currentRouteName(), [
            'file'    => $exception->getFile(),
            'line'    => $exception->getLine(),
            'message' => $exception->getMessage(),
            'trace'   => $exception->getTrace()
        ]);
    }

    /**
     * Method that would check if getStatusCode/getCode method is available and return relevant error code
     *
     * @param exception $e
     * @return integer $error_code
     */
    public static function getCode($e)
    {
        $error_code = method_exists($e, 'getStatusCode')
            ? $e->getStatusCode()
            : (method_exists($e, 'getCode') ? $e->getCode() : '0');
        return $error_code;
    }
}