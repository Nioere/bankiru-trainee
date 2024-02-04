<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class OpenApiController extends AbstractController
{
    #[Route('/api/v1/openapi/', name: 'api_v1_openapi', methods: [Request::METHOD_GET])]
    public function openapi(): Response
    {
        $jsonFilePath = dirname(__DIR__, 2) . '/public/openapi/openapi.json';

        return new JsonResponse(file_get_contents($jsonFilePath), Response::HTTP_OK, [], true);
    }

    #[Route('/', name: 'openapi_index', methods: [Request::METHOD_GET])]
    public function index(): Response
    {
        return new Response(
            '<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Swagger UI</title>
    <link rel="stylesheet" type="text/css" href="./openapi/swagger-ui.css" />
    <link rel="stylesheet" type="text/css" href="./openapi/index.css" />
    <link rel="icon" type="image/png" href="./openapi/favicon-32x32.png" sizes="32x32" />
    <link rel="icon" type="image/png" href="./openapi/favicon-16x16.png" sizes="16x16" />
  </head>

  <body>
    <div id="swagger-ui"></div>
    <script src="./openapi/swagger-ui-bundle.js" charset="UTF-8"> </script>
    <script src="./openapi/swagger-ui-standalone-preset.js" charset="UTF-8"> </script>
    <script src="./openapi/swagger-initializer.js" charset="UTF-8"> </script>
  </body>
</html>',
        );
    }
}
