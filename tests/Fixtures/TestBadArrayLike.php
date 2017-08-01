<?php

namespace Bolt\Collection\Tests\Fixtures;

class TestBadArrayLike extends TestArrayLike
{
    public function offsetGet($offset) // Note no "&"
    {
        // @codingStandardsIgnoreLine
        @trigger_error('Some notice', E_USER_NOTICE);

        return $this->items[$offset];
    }
}
