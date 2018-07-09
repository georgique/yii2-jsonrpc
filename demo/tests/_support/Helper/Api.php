<?php
namespace tests\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\Exception\ModuleException;
use tests\JsonEquals;

class Api extends \Codeception\Module
{
    /**
     * @param array $json
     */
    public function seeResponseEqualsJson($json = [])
    {
        try {
            $response = $this->getModule('REST')->response;
        } catch (ModuleException $e) {
            $response = null;
        }

        \PHPUnit\Framework\Assert::assertThat(
            $response,
            new JsonEquals($json)
        );
    }
}
