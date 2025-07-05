<?php

// here you can define custom actions
// all public methods declared in helper class will be available in $I

namespace Helper;

use Codeception\Exception\ModuleException;
use Codeception\Module;
use georgique\yii2\jsonrpc\tests\support\JsonEquals;
use PHPUnit\Framework\Assert;

class Api extends Module
{
    /**
     * @param array $json
     */
    public function seeResponseEqualsJson($json = [])
    {
        try {
            $response = $this->getModule('REST')->response;
        } catch (ModuleException) {
            $response = null;
        }

        Assert::assertThat(
            $response,
            new JsonEquals($json)
        );
    }
}
