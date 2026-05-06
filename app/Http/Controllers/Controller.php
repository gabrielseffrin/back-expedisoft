<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;
#[OA\Info(
    version: "1.0.0",
    description: "Documentação da API de integração do sistema Expedisoft",
    title: "API Expedisoft",
    contact: new OA\Contact(email: "suporte@expedisoft.com")
)]
#[OA\Server(
    url: "http://localhost",
    description: "Servidor API Local"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer"
)]
#[OA\SecurityScheme(
    securityScheme: "ApiKeyAuth",
    type: "apiKey",
    in: "header",
    name: "X-API-KEY"
)]
abstract class Controller
{
}
