<?php

declare(strict_types=1);

namespace Tests\Api\Example;

use Symfony\Component\HttpFoundation\Response;
use Tests\Support\ApiTester;

class ExampleApiCest
{
    public function tryExampleApi(ApiTester $I)
    {
        $I->sendGet('/example/');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
        ]);
    }
}
