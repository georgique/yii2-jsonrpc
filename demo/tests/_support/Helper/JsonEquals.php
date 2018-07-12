<?php

namespace tests\Helper;

class JsonEquals extends JsonEqualsWrapper
{
    /**
     * @inheritdoc
     */
    protected function matches($other)
    {
        return parent::_matches($other);
    }
}
