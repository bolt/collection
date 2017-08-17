<?php

namespace Bolt\Collection\Tests\Fixtures;

class TestBadReferenceExpressionArrayLike extends TestArrayLike
{
    public function &offsetGet($offset)
    {
        // Bad: Only variable references should be returned by reference
        return isset($this->items[$offset]) ? $this->items[$offset] : null;
    }
}
