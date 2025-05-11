<?php

namespace App\docs;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="My Translation Service API",
 *     version="1.0.0",
 *     description="Translation Service API Documentation"
 * )
 * 
 * @OA\SecurityScheme(
 *     type="http",
 *     description="Use a Bearer token to access this API",
 *     name="Authorization",
 *     in="header",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     securityScheme="sanctum"
 * )
 */
class OpenApiSpec
{
}
