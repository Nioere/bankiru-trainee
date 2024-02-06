<?php

declare(strict_types=1);

namespace Tests\Api\CRUDApiCest;

use Tests\Support\ApiTester;

class CRUDApiCest
{
    protected $userId;
    protected $petId;

    public function _before(ApiTester $I)
    {
    }

    public function tryToTestUserCreate(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('/user', [
            'email' => 'test@example.com',
            'name' => 'Test User',
            'petIds' => [0],
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

    public function tryToTestUserIndex(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet('/user');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'id' => 'integer',
            'email' => 'string',
            'name' => 'string',
            'createdAt' => 'string:date',
            'updatedAt' => 'string:date',
            'petIds' => 'array',
        ]);
    }

    public function tryToTestUserShow(ApiTester $I)
    {
        $I->sendGet("/user/{$this->userId}");
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'id' => $this->userId,
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);
        $I->seeResponseMatchesJsonType([
            'id' => 'integer',
            'email' => 'string',
            'name' => 'string',
            'createdAt' => 'string:date',
            'updatedAt' => 'string:date|null',
            'petIds' => 'array|null',
        ]);
    }

    public function tryToTestUserUpdate(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost("/user/{$this->userId}", json_encode([
            'email' => 'updated@example.com',
            'name' => 'Updated User',
            'petIds' => [],
        ]));
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'id' => $this->userId,
            'email' => 'updated@example.com',
            'name' => 'Updated User',
        ]);
        $I->seeResponseMatchesJsonType([
            'id' => 'integer',
            'email' => 'string',
            'name' => 'string',
            'createdAt' => 'string:date',
            'updatedAt' => 'string:date',
            'petIds' => 'array',
        ]);
    }

    public function tryToTestPetCreate(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('/pet', json_encode([
            'userId' => $this->userId,
            'name' => 'Test Pet',
            'description' => 'This is a test pet',
        ]));
        $I->seeResponseCodeIs(201);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['userId' => $this->userId]);
        $I->seeResponseMatchesJsonType([
            'id' => 'integer',
            'userId' => 'integer',
            'name' => 'string',
            'description' => 'string',
            'createdAt' => 'string:date',
            'updatedAt' => 'string:date',
        ]);
        $this->petId = $I->grabDataFromResponseByJsonPath('$.id')[0];
    }

    public function tryToTestPetIndex(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet('/pet');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'id' => 'integer',
            'userId' => 'integer',
            'name' => 'string',
            'description' => 'string',
            'createdAt' => 'string:date',
            'updatedAt' => 'string:date',
        ]);
    }

    public function tryToTestPetShow(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet("/pet/{$this->petId}");
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'id' => $this->petId,
            'userId' => $this->userId,
            'name' => 'Test Pet',
        ]);
        $I->seeResponseMatchesJsonType([
            'id' => 'integer',
            'userId' => 'integer',
            'name' => 'string',
            'description' => 'string',
            'createdAt' => 'string:date',
            'updatedAt' => 'string:date',
        ]);
    }

    public function tryToTestPetUpdate(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost("/pet/{$this->petId}", json_encode([
            'userId' => $this->userId, // Убедитесь, что userId передается, если это необходимо
            'name' => 'Updated Pet',
            'description' => 'This is an updated test pet',
            'createdAt' => date('c'), // Добавьте дату создания питомца, если это требуется
            'updatedAt' => date('c'), // Добавьте текущую дату в формате ISO 8601
        ]));
        $I->seeResponseCodeIs(201);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'id' => $this->petId,
            'name' => 'Updated Pet',
        ]);
        $I->seeResponseMatchesJsonType([
            'id' => 'integer',
            'userId' => 'integer',
            'name' => 'string',
            'description' => 'string',
            'createdAt' => 'string:date',
            'updatedAt' => 'string:date',
        ]);
    }

    public function tryToTestPetDelete(ApiTester $I)
    {
        $I->sendDelete("/pet/{$this->petId}");
        $I->seeResponseCodeIs(204);
    }

    public function tryToTestUserDelete(ApiTester $I)
    {
        $I->sendDelete("/user/{$this->userId}");
        $I->seeResponseCodeIs(204);
    }
}
