<?php

declare(strict_types=1);

namespace Tests\Api\PetApiCest;

use Symfony\Component\HttpFoundation\Response;
use Tests\Support\ApiTester;

class PetApiCest
{
    private $petId;

    public function _before(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/pet', [
            'userId' => 1,
            'name' => 'Test Pet',
            'description' => 'This is a test pet',
            'createdAt' => '2024-02-04T04:29:00Z',
            'updatedAt' => '2024-02-04T04:29:00Z',
        ]);
        $I->seeResponseCodeIs(Response::HTTP_CREATED);
        $I->seeResponseIsJson();

        $response = $I->grabResponse();
        $responseData = json_decode($response, true);

        $this->petId = $responseData['id'];
    }

    public function tryGetPet(ApiTester $I)
    {
        $I->sendGET("/pet/{$this->petId}");
        $I->seeResponseCodeIs(Response::HTTP_OK);
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

    public function tryGetListOfPets(ApiTester $I)
    {
        $I->sendGET('/pet');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            [
                'userId' => 1,
                'name' => 'Test Pet',
                'description' => 'This is a test pet',
            ],
        ]);
    }

    public function tryToUpdatePet(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST("/pet/{$this->petId}", [
            'userId' => 1,
            'name' => 'Updated Test Pet',
            'description' => 'This is an updated test pet',
            'createdAt' => '2024-02-04T04:29:00Z',
            'updatedAt' => '2024-02-04T04:29:00Z',
        ]);
        $I->seeResponseCodeIs(Response::HTTP_CREATED);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'id' => $this->petId,
            'name' => 'Updated Test Pet',
            'description' => 'This is an updated test pet',
        ]);
    }

    public function tryToDeletePet(ApiTester $I)
    {
        $I->sendDELETE("/pet/{$this->petId}");
        $I->seeResponseCodeIs(Response::HTTP_NO_CONTENT);
    }

}
