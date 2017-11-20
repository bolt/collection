<?php

declare(strict_types=1);

namespace Bolt\Collection\Tests;

use ArrayObject;
use Bolt\Collection\MutableBag;
use PHPUnit\Framework\TestCase;

/**
 * Tests for mutating methods previously on Bag but moved to MutableBag.
 *
 * @mixin TestCase
 */
trait BagLegacyTrait
{
    protected function createBag($items = [])
    {
        return new MutableBag($items);
    }

    public function testAdd(): void
    {
        $bag = $this->createBag();

        $bag->add('foo');
        $bag->add('bar');

        $this->assertEquals(['foo', 'bar'], $bag->toArray());
    }

    public function testPrepend(): void
    {
        $bag = $this->createBag();

        $bag->prepend('foo');
        $bag->prepend('bar');

        $this->assertEquals(['bar', 'foo'], $bag->toArray());
    }

    public function testSet(): void
    {
        $bag = $this->createBag();

        $bag->set('foo', 'bar');

        $this->assertEquals(['foo' => 'bar'], $bag->toArray());
    }

    public function testSetPath(): void
    {
        $bag = $this->createBag([
            'items' => new ArrayObject([
                'foo' => 'bar',
            ]),
        ]);

        $bag->setPath('items/hello', 'world');
        $bag->setPath('items/colors/[]', 'red');
        $bag->setPath('items/colors/[]', 'blue');

        $this->assertEquals('world', $bag->getPath('items/hello'));

        $this->assertEquals(['red', 'blue'], $bag->getPath('items/colors'));
    }

    public function testClear(): void
    {
        $bag = $this->createBag(['foo', 'bar']);

        $bag->clear();

        $this->assertTrue($bag->isEmpty());
    }

    public function testRemove(): void
    {
        $bag = $this->createBag(['foo' => 'bar']);

        $this->assertEquals('bar', $bag->remove('foo'));
        $this->assertFalse($bag->has('foo'));

        $this->assertNull($bag->remove('derp'));
        $this->assertEquals('default', $bag->remove('derp', 'default'));
    }

    public function testRemoveItem(): void
    {
        $bag = $this->createBag(['foo', 'bar']);

        $bag->removeItem('bar');

        $this->assertFalse($bag->hasItem('bar'));
    }

    public function testRemoveFirst(): void
    {
        $bag = $this->createBag(['foo', 'bar']);

        $this->assertEquals('foo', $bag->removeFirst());
        $this->assertEquals('bar', $bag->removeFirst());
        $this->assertNull($bag->removeFirst());
        $this->assertEmpty($bag);
    }

    public function testRemoveLast(): void
    {
        $bag = $this->createBag(['foo', 'bar']);

        $this->assertEquals('bar', $bag->removeLast());
        $this->assertEquals('foo', $bag->removeLast());
        $this->assertNull($bag->removeLast());
        $this->assertEmpty($bag);
    }

    public function testOffsetGet(): void
    {
        $bag = $this->createBag(['foo' => 'bar']);

        $this->assertEquals('bar', $bag['foo']);
        $this->assertNull($bag['nope']);
    }

    public function testOffsetGetByReference(): void
    {
        $bag = $this->createBag(['arr' => ['1']]);

        // Assert arrays are able to be modified by reference.
        $errors = new \ArrayObject();
        set_error_handler(function ($type, $message) use ($errors): void {
            $errors[] = [$type, $message];
        });

        $arr = &$bag['arr'];
        $arr[] = '2';

        restore_error_handler();
        $this->assertEmpty($errors);

        $this->assertEquals(['1', '2'], $bag['arr']);
    }

    public function testOffsetSet(): void
    {
        $bag = $this->createBag();

        $bag['foo'] = 'bar';
        $bag[] = 'hello';
        $bag[] = 'world';

        $this->assertEquals('bar', $bag['foo']);
        $this->assertEquals('hello', $bag[0]);
        $this->assertEquals('world', $bag[1]);
    }

    public function testOffsetUnset(): void
    {
        $bag = $this->createBag(['foo' => 'bar']);

        unset($bag['foo']);

        $this->assertFalse($bag->has('foo'));
    }
}
