<?php

declare(strict_types=1);

namespace Tests\Api\CRUDApiCest;

use Tests\Support\ApiTester;

class CRUDApiCest
{
    protected $userId;

    public function _before(ApiTester $I)
    {
    }

    public function tryToTestCreate(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('/user', [
            'email' => 'test@example.com',
            'name' => 'Test User',
            'petIds' => [],
        ]);
        $I->seeResponseCodeIs(201);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'id' => 'integer',
            'email' => 'string',
            'name' => 'string',
            'createdAt' => 'string:date',
            'updatedAt' => 'string:date',
            'petIds' => 'array',
        ]);
        $this->userId = $I->grabDataFromResponseByJsonPath('$.id')[0];
    }
}
