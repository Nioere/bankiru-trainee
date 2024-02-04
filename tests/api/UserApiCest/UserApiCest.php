<?php

declare(strict_types=1);

namespace Tests\Api\UserApiCest;

use Symfony\Component\HttpFoundation\Response;
use Tests\Support\ApiTester;

class UserApiCest
{
    private $userId;

    public function _before(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/user', [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);
        $I->seeResponseCodeIs(Response::HTTP_CREATED);
        $I->seeResponseIsJson();

        $response = $I->grabResponse();
        $responseData = json_decode($response, true);

        $this->userId = $responseData['id'];
    }

    public function tryGetUser(ApiTester $I)
    {
        $I->sendGET("/user/{$this->userId}");
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'id' => 'integer',
            'email' => 'string',
            'name' => 'string',
            'createdAt' => 'string:date',
            'updatedAt' => 'string:date|null',
            'petIds' => 'array|null',
        ]);
    }

    public function tryGetListOfUsers(ApiTester $I)
    {
        $I->sendGET('/user');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            [
                'email' => 'test@example.com',
                'name' => 'Test User',
            ],
        ]);
    }

    public function tryToUpdateUser(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST("/user/{$this->userId}", [
            'email' => 'updated@example.com',
            'name' => 'Updated User',
        ]);
        $I->seeResponseCodeIs(Response::HTTP_NO_CONTENT);
    }

    public function tryToDeleteUser(ApiTester $I)
    {
        $I->sendDELETE("/user/{$this->userId}");
        $I->seeResponseCodeIs(Response::HTTP_NO_CONTENT);
    }
}
