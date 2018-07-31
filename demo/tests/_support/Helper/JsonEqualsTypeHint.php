<?php

namespace tests\Helper;

/**
 * Wrapper for PHP 7 type-hinted PHPUnit method
 *
 * Class JsonEquals7
 * @package tests
 */
class JsonEqualsTypeHint extends JsonEqualsWrapper
{
    /**
     * @inheritdoc
     */
    protected function matches($other) : bool
    {
        return parent::_matches($other);
    }
}
