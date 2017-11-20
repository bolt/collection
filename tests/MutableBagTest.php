<?php

declare(strict_types=1);

namespace Bolt\Collection\Tests;

use ArrayObject;
use Bolt\Collection\MutableBag;

class MutableBagTest extends BagTest
{
    use BagLegacyTrait;

    /** @var string|MutableBag */
    protected $cls = MutableBag::class;

    protected function createBag($items = [])
    {
        return new MutableBag($items);
    }

    public function testRemovePath(): void
    {
        $bag = $this->createBag(
            [
                'items' => new ArrayObject(
                    [
                        'foo' => 'bar',
                    ]
                ),
            ]
        );

        $this->assertSame('bar', $bag->removePath('items/foo'));
        $this->assertNull($bag->removePath('items/foo'));
    }
}
