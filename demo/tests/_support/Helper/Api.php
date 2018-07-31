<?php
namespace tests\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\Exception\ModuleException;

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
            version_compare(PHP_VERSION, '7.1', '>=')
                ? new JsonEqualsTypeHint($json)
                : new JsonEquals($json)
        );
    }
}
