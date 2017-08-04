<?php

namespace Bolt\Collection\Tests;

use ArrayObject;
use Bolt\Collection\Bag;

class BagTest extends ImmutableBagTest
{
    /** @var string|Bag */
    protected $cls = Bag::class;

    protected function createBag($items = [])
    {
        return new Bag($items);
    }

    // region Methods returning a new bag

    public function testCountValues()
    {
        $bag = $this->createBag([
            'red',
            'red',
            'blue',
        ]);

        $actual = $bag->countValues();
        $this->assertBagResult(['red' => 2, 'blue' => 1], $bag, $actual);
    }

    // endregion

    // region Mutating Methods (Deprecated)

    /**
     * @group legacy
     */
    public function testAdd()
    {
        $bag = $this->createBag();

        $bag->add('foo');
        $bag->add('bar');

        $this->assertEquals(['foo', 'bar'], $bag->toArray());
    }

    /**
     * @group legacy
     */
    public function testPrepend()
    {
        $bag = $this->createBag();

        $bag->prepend('foo');
        $bag->prepend('bar');

        $this->assertEquals(['bar', 'foo'], $bag->toArray());
    }

    /**
     * @group legacy
     */
    public function testSet()
    {
        $bag = $this->createBag();

        $bag->set('foo', 'bar');

        $this->assertEquals(['foo' => 'bar'], $bag->toArray());
    }

    /**
     * @group legacy
     */
    public function testSetPath()
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

    /**
     * @group legacy
     */
    public function testClear()
    {
        $bag = $this->createBag(['foo', 'bar']);

        $bag->clear();

        $this->assertTrue($bag->isEmpty());
    }

    /**
     * @group legacy
     */
    public function testRemove()
    {
        $bag = $this->createBag(['foo' => 'bar']);

        $this->assertEquals('bar', $bag->remove('foo'));
        $this->assertFalse($bag->has('foo'));

        $this->assertNull($bag->remove('derp'));
        $this->assertEquals('default', $bag->remove('derp', 'default'));
    }

    /**
     * @group legacy
     */
    public function testRemoveItem()
    {
        $bag = $this->createBag(['foo', 'bar']);

        $bag->removeItem('bar');

        $this->assertFalse($bag->hasItem('bar'));
    }

    /**
     * @group legacy
     */
    public function testRemoveFirst()
    {
        $bag = $this->createBag(['foo', 'bar']);

        $this->assertEquals('foo', $bag->removeFirst());
        $this->assertEquals('bar', $bag->removeFirst());
        $this->assertNull($bag->removeFirst());
        $this->assertTrue($bag->isEmpty());
    }

    /**
     * @group legacy
     */
    public function testRemoveLast()
    {
        $bag = $this->createBag(['foo', 'bar']);

        $this->assertEquals('bar', $bag->removeLast());
        $this->assertEquals('foo', $bag->removeLast());
        $this->assertNull($bag->removeLast());
        $this->assertTrue($bag->isEmpty());
    }

    // endregion

    // region Internal Methods

    /**
     * @group legacy
     */
    public function testOffsetGet()
    {
        $bag = $this->createBag(['foo' => 'bar']);

        $this->assertEquals('bar', $bag['foo']);
        $this->assertNull($bag['nope']);
    }

    /**
     * @group legacy
     */
    public function testOffsetGetByReference()
    {
        $bag = $this->createBag(['arr' => ['1']]);

        // Assert arrays are not able to be modified by reference.
        $errors = new \ArrayObject();
        set_error_handler(function ($type, $message) use ($errors) {
            $errors[] = [$type, $message];
        });

        $arr = &$bag['arr'];
        $arr[] = '2';

        restore_error_handler();
        $this->assertEmpty($errors->getArrayCopy());

        $this->assertEquals(['1', '2'], $bag['arr']);
    }

    /**
     * @group legacy
     */
    public function testOffsetSet()
    {
        $bag = $this->createBag();

        $bag['foo'] = 'bar';
        $bag[] = 'hello';
        $bag[] = 'world';

        $this->assertEquals('bar', $bag['foo']);
        $this->assertEquals('hello', $bag[0]);
        $this->assertEquals('world', $bag[1]);
    }

    /**
     * @group legacy
     */
    public function testOffsetUnset()
    {
        $bag = $this->createBag(['foo' => 'bar']);

        unset($bag['foo']);

        $this->assertFalse($bag->has('foo'));
    }

    // endregion
}
