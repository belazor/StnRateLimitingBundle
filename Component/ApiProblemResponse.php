<?php

namespace Stn\RateLimitingBundle\Component;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Represents an ApiProblem response payload.
 */
class ApiProblemResponse extends JsonResponse
{
    /**
     * @param ApiProblem $apiProblem
     */
    public function __construct(ApiProblem $apiProblem)
    {
        parent::__construct($apiProblem->toArray(), $apiProblem->status, [
            'Content-Type' => ApiProblem::CONTENT_TYPE
        ]);
    }
}
