<?php
use OpenApi\Annotations as OA;
/**
 * @OA\Info(
 *     description="API Documentation",
 *     version="1.0.0",
 *     title="Marion Basic API"
 * )
 * @OA\SecurityScheme(
 *   securityScheme="apiKey",
 *   type="apiKey",
 *   name="api-key",
 *   in="header"
 * ),
 * @OA\SecurityScheme(
 *      securityScheme="bearerAuth",
 *      in="header",
 *      name="Authorization",
 *      type="http",
 *      scheme="Bearer",
 *      bearerFormat="JWT",
 * )
 */
class OpenApiController{

}