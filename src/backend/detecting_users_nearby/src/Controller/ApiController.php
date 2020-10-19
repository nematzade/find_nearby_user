<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{

    /**
     * @var integer HTTP status code - 200 (ok) by default
     */
    protected $statusCode = 200;

    /**
     * @param int $statusCode
     */
    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @param $data
     * @param array $headers
     * @return JsonResponse
     */
    public function response($data,$headers = [])
    {
        return new JsonResponse($data,$this->getStatusCode(),$headers);
    }

    /**
     * @param $errors
     * @param array $headers
     * @return JsonResponse
     */
    public function respondWithErrors($errors,$headers = [])
    {
        $data = [
            'status' => $this->getStatusCode(),
            'errors' => $errors,
        ];

        return new JsonResponse($data,$this->getStatusCode(),$headers);
    }

    /**
     * @param $success
     * @param array $headers
     * @return JsonResponse
     */
    public function respondWithSuccess($success,$headers = [])
    {
        $data = [
            'status' => $this->getStatusCode(),
            'success' => $success,
        ];

        return new JsonResponse($data,$this->getStatusCode(),$headers);
    }

    /**
     * @param string $message
     * @return JsonResponse
     */
    public function respondUnauthorized($message = 'Not authorized!')
    {
        $this->setStatusCode(401);
        return $this->respondWithErrors($message);
    }

    /**
     * @param string $message
     * @return JsonResponse
     */
    public function respondValidationError($message = 'Validation Errors')
    {
        $this->setStatusCode(422);
        return $this->respondWithErrors($message);
    }

    /**
     * @param string $message
     * @return JsonResponse
     */
    public function respondNotFound($message = 'Not Found')
    {
        $this->setStatusCode(404);
        return $this->respondWithErrors($message);
    }

    /**
     * @param array $data
     * @return JsonResponse
     */
    public function respondCreated($data = [])
    {
        $this->setStatusCode(201);
        return $this->respondWithErrors($data);
    }

    protected function transformJsonBody(Request $request)
    {
        $data = json_decode($request->getContent(),true);
        if ($data === null){
            return $request;
        }

        $request->request->replace($data);

        return $request;
    }
}
