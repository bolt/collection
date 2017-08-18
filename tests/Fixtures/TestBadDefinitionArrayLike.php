<?php

namespace Bolt\Collection\Tests\Fixtures;

class TestBadDefinitionArrayLike extends TestArrayLike
{
    public function offsetGet($offset) // Bad: no "&"
    {
        return $this->items[$offset];
    }
}
