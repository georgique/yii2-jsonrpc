<?php
namespace tests\Helper;

use Codeception\PHPUnit\Constraint\JsonContains;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\ArrayComparator;
use SebastianBergmann\Comparator\Factory;
use Codeception\Util\JsonArray;

class JsonEqualsWrapper extends JsonContains
{
    protected function _matches($other)
    {
        $jsonResponseArray = new JsonArray($other);
        if (!is_array($jsonResponseArray->toArray())) {
            throw new \PHPUnit\Framework\AssertionFailedError('JSON response is not an array: ' . $other);
        }

        $comparator = new ArrayComparator();
        $comparator->setFactory(new Factory);
        try {
            $comparator->assertEquals($this->expected, $jsonResponseArray->toArray());
        } catch (ComparisonFailure $failure) {
            throw new \PHPUnit\Framework\ExpectationFailedException(
                "Response JSON does not contain the provided JSON\n",
                $failure
            );
        }

        return true;
    }
}
