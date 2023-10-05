<?php

namespace App\Exceptions;

use App\Traits\ApiResponser;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

class Handler extends ExceptionHandler
{
    use ApiResponser;

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
        $this->renderable(function (ValidationException $e, $request) {
            return $this->convertValidationExceptionToResponse($e, $request);
        });

        $this->renderable(function (NotFoundHttpException $e, $request) {
            $model = strtolower(class_basename($e->getPrevious()->getModel()));
            return $this->errorResponse("No existe ninguna instancia de $model con el ID especificado", 404);
        });

        $this->renderable(function (AuthenticationException $e, $request) {
            return $this->unauthenticated($request, $e);
        });

        $this->renderable(function (AuthorizationException $e, $request) {
            return $this->errorResponse('No posee permisos para ejecutar esta accion', 403);
        });

        $this->renderable(function (MethodNotAllowedException $e, $request) {
            return $this->errorResponse('El método especificado en la petición no es válido', 405);
        });

        $this->renderable(function (HttpException $e, $request) {
            return $this->errorResponse($e->getMessage(), $e->getStatusCode());
        });

        $this->renderable(function (QueryException $e, $request) {
            $code = $e->errorInfo[1];

            if ($code == 1451) {
                return $this->errorResponse('No se puede eliminar el registro porque completa otra información', 409);
            }
        });
    }

    protected function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
        $errors = $e->validator->errors()->getMessages();
        return $this->errorResponse($errors, 422);
    }

    protected function unauthenticated($request, AuthenticationException $e)
    {
        return $this->errorResponse('No autenticado.', 401);
    }
}
