<?php

class LoginCest
{
    public function loginSuccessfully(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->see('_');
    }
}
