<?php

namespace Bolt\Collection\Tests;

use Bolt\Collection\MutableBag;

class MutableBagTest extends BagTest
{
    /** @var string|MutableBag */
    protected $cls = MutableBag::class;

    protected function createBag($items = [])
    {
        return new MutableBag($items);
    }
}
