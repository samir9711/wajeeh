<?php

namespace App\Http\Traits;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Spatie\Permission\Exceptions\UnauthorizedException as SpatieUnauthorized;

trait GeneralTrait
{
    public function apiResponse($data = null, bool $status = true, $error = null, $statusCode = 200)
    {
        $array = [
            'data' => $data,
            'status' => $status ,
            'error' => $error,
            'statusCode' => $statusCode
        ];
        return response($array, $statusCode);
    }

    public function unAuthorizeResponse($message='Unauthorize')
    {
        return $this->apiResponse(null, 0, $message, 401);
    }

    public function notFoundResponse($more)
    {
        return $this->apiResponse(null, 0, $more, 404);
    }

    public function requiredField($message)
    {
        return $this->apiResponse(null, false, $message, 400);
    }

    public function internalServer($message)
    {
        return $this->apiResponse(null, false,$message, 500);
    }

    public function forbiddenResponse($message = "Forbidden")
    {
        return $this->apiResponse(null, false, $message, 403);
    }

    public function handleException(\Throwable $e)
    {
        if ($e instanceof ModelNotFoundException) {
            $modelName = class_basename($e->getModel());
            return $this->notFoundResponse("$modelName not found");

        } elseif ($e instanceof ValidationException) {
            $errors = $e->validator->errors();
            return response()->json([
                'data' => null,
                'status' => false,
                'error' => $errors->first(),
                'errors' => $errors->toArray(),
                'statusCode' => 422,
            ], 422);

        } elseif ($e instanceof AuthenticationException) {
            return $this->unAuthorizeResponse(__('messages.unauthenticated'));

        } elseif ($e instanceof SpatieUnauthorized) {
            return $this->forbiddenResponse(__('messages.forbidden_role_or_permission'));

        } elseif ($e instanceof AuthorizationException) {
            return $this->forbiddenResponse($e->getMessage() ?: __('messages.forbidden'));

        } elseif ($e instanceof HttpResponseException) {
            return $e->getResponse();

        } elseif ($e instanceof QueryException) {
            return $this->handleQueryException($e);

        } else {
            return $this->apiResponse(null, false, $e->getMessage(), 500);
        }
    }

    protected function handleQueryException(QueryException $e)
    {
        $errorCode = (isset($e->errorInfo[1]))? $e->errorInfo[1] : 0;

        switch ($errorCode) {
            case 1062: // Duplicate entry
                return $this->requiredField(__('messages.db_duplicate_entry'));

            case 1451: // FK restricted
                return $this->requiredField(__('messages.db_fk_restricted'));

            case 1452: // FK violation
                return $this->requiredField(__('messages.db_fk_violation'));

            default:
                return $this->internalServer("Database error: " . $e->getMessage());
        }
    }
}
