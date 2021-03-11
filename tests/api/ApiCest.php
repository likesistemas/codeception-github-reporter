<?php

use Codeception\Util\HttpCode;

class ApiCest
{
    public function openHome(ApiTester $I)
    {
        $I->sendGet('/');
        $I->seeResponseCodeIs(HttpCode::OK);
    }
}
