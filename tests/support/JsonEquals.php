<?php

namespace georgique\yii2\jsonrpc\tests\support;

use Codeception\PHPUnit\Constraint\JsonContains;
use Codeception\Util\JsonArray;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\Comparator\ArrayComparator;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\Factory;

class JsonEquals extends JsonContains
{

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param mixed $other Value or object to evaluate.
     *
     * @return bool
     */
    protected function matches($other): bool
    {
        $jsonResponseArray = new JsonArray($other);
        if (!is_array($jsonResponseArray->toArray())) {
            throw new AssertionFailedError('JSON response is not an array: ' . $other);
        }

        $comparator = new ArrayComparator();
        $comparator->setFactory(new Factory());
        try {
            $comparator->assertEquals($this->expected, $jsonResponseArray->toArray());
        } catch (ComparisonFailure $failure) {
            throw new ExpectationFailedException(
                "Response JSON does not contain the provided JSON\n",
                $failure
            );
        }

        return true;
    }
}
