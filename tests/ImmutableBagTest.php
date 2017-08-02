<?php

namespace Bolt\Collection\Tests;

use ArrayObject;
use Bolt\Collection\Bag;
use Bolt\Collection\ImmutableBag;
use PHPUnit\Framework\TestCase;

class ImmutableBagTest extends TestCase
{
    /** @var string|ImmutableBag class used for static creation methods and instance of assertions */
    protected $cls = ImmutableBag::class;

    protected function createBag($items = [])
    {
        return new ImmutableBag($items);
    }

    // region Creation / Unwrapping Methods

    public function provideFromAndToArray()
    {
        return [
            'bag'         => [$this->createBag(['foo' => 'bar'])],
            'traversable' => [new ArrayObject(['foo' => 'bar'])],
            'null'        => [null, []],
            'stdClass'    => [json_decode(json_encode(['foo' => 'bar']))],
            'array'       => [['foo' => 'bar']],
            'mixed'       => ['derp', ['derp']],
        ];
    }

    /**
     * @dataProvider provideFromAndToArray
     *
     * @param mixed $input
     * @param array $output
     */
    public function testFromAndToArray($input, $output = ['foo' => 'bar'])
    {
        $cls = $this->cls;
        $actual = $cls::from($input)->toArray();
        $this->assertSame($output, $actual);
    }

    public function testFromRecursive()
    {
        $a = [
            'foo'       => 'bar',
            'items'     => new ArrayObject(['hello' => 'world']),
            'std class' => json_decode(json_encode([
                'why use' => 'these',
            ])),
        ];

        $cls = $this->cls;
        $bag = $cls::fromRecursive($a);

        $bagArr = $bag->toArray();
        $this->assertEquals('bar', $bagArr['foo']);

        $this->assertInstanceOf($cls, $bagArr['items']);
        /** @var ImmutableBag $items */
        $items = $bagArr['items'];
        $this->assertEquals(['hello' => 'world'], $items->toArray());

        $this->assertInstanceOf($cls, $bagArr['std class']);
        /** @var ImmutableBag $stdClass */
        $stdClass = $bagArr['std class'];
        $this->assertEquals(['why use' => 'these'], $stdClass->toArray());
    }

    public function testToArrayRecursive()
    {
        $bag = $this->createBag([
            'foo'    => 'bar',
            'colors' => $this->createBag(['red', 'blue']),
            'array'  => ['hello', 'world'],
        ]);
        $expected = [
            'foo'    => 'bar',
            'colors' => ['red', 'blue'],
            'array'  => ['hello', 'world'],
        ];

        $arr = $bag->toArrayRecursive();

        $this->assertEquals($expected, $arr);
    }

    public function testCombine()
    {
        $cls = $this->cls;
        $actual = $cls::combine(['red', 'green'], ['bad', 'good'])->toArray();
        $expected = [
            'red'   => 'bad',
            'green' => 'good',
        ];

        $this->assertEquals($expected, $actual);
    }

    public function testCombineEmpty()
    {
        $cls = $this->cls;
        $actual = $cls::combine([], [])->toArray();

        $this->assertEquals([], $actual);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCombineDifferentSizes()
    {
        $cls = $this->cls;
        $cls::combine(['derp'], ['wait', 'wut']);
    }

    // endregion

    // region Methods returning a single value

    public function testHas()
    {
        $bag = $this->createBag(['foo' => 'bar', 'null' => null]);

        $this->assertTrue($bag->has('foo'));
        $this->assertTrue($bag->has('null'));

        $this->assertFalse($bag->has('derp'));
    }

    public function testHasPath()
    {
        $bag = $this->createBag([
            'items' => new ArrayObject([
                'foo' => 'bar',
            ]),
        ]);

        $this->assertTrue($bag->hasPath('items/foo'));

        $this->assertFalse($bag->hasPath('items/derp'));
        $this->assertFalse($bag->hasPath('derp'));
    }

    public function testHasItem()
    {
        $foo = new ArrayObject();
        $bag = $this->createBag([
            'foo'   => 'bar',
            'items' => $foo,
        ]);

        $this->assertTrue($bag->hasItem('bar'));
        $this->assertTrue($bag->hasItem($foo));

        $this->assertFalse($bag->hasItem('derp'));
    }

    public function testGet()
    {
        $bag = $this->createBag([
            'foo'  => 'bar',
            'null' => null,
        ]);

        $this->assertEquals('bar', $bag->get('foo'));
        $this->assertNull($bag->get('null', 'default'));
        $this->assertEquals('default', $bag->get('derp', 'default'));
    }

    public function testGetPath()
    {
        $bag = $this->createBag([
            'items' => new ArrayObject([
                'foo' => 'bar',
            ]),
        ]);

        $this->assertEquals('bar', $bag->getPath('items/foo'));

        $this->assertNull($bag->getPath('derp/derp'));
        $this->assertEquals('default', $bag->getPath('derp/derp', 'default'));
    }

    public function testCount()
    {
        $bag = $this->createBag(['foo', 'bar']);

        $this->assertEquals(2, count($bag));
    }

    public function testEmpty()
    {
        $bag = $this->createBag(['foo', 'bar']);
        $this->assertFalse($bag->isEmpty());

        $empty = $this->createBag();
        $this->assertTrue($empty->isEmpty());
    }

    public function testFirst()
    {
        $bag = $this->createBag(['first', 'second']);
        $this->assertEquals('first', $bag->first());

        $empty = $this->createBag();
        $this->assertFalse($empty->first());
    }

    public function testLast()
    {
        $bag = $this->createBag(['first', 'second']);
        $this->assertEquals('second', $bag->last());

        $empty = $this->createBag();
        $this->assertFalse($empty->last());
    }

    public function testJoin()
    {
        $bag = $this->createBag(['first', 'second', 'third']);
        $this->assertEquals('first, second, third', $bag->join(', '));

        $empty = $this->createBag();
        $this->assertEquals('', $empty->join(', '));
    }

    public function testSum()
    {
        $bag = $this->createBag([3, 4]);
        $this->assertEquals(7, $bag->sum());

        $empty = $this->createBag();
        $this->assertEquals(0, $empty->sum());

        $dumb = $this->createBag(['wut']);
        $this->assertEquals(0, $dumb->sum());
    }

    public function testProduct()
    {
        $bag = $this->createBag([3, 4]);
        $this->assertEquals(12, $bag->product());

        $empty = $this->createBag();
        $this->assertEquals(1, $empty->product());

        $dumb = $this->createBag(['wut']);
        $this->assertEquals(0, $dumb->product());
    }

    /**
     * @dataProvider \Bolt\Collection\Tests\ArrTest::provideIsIndexed
     *
     * @param array $data
     * @param bool  $isIndexed
     */
    public function testIsAssociativeAndIndexed($data, $isIndexed)
    {
        $bag = $this->createBag($data);

        $this->assertEquals($isIndexed, $bag->isIndexed());
        $this->assertEquals(!$isIndexed, $bag->isAssociative());
    }

    // endregion

    // region Methods returning a new bag

    public function testMutable()
    {
        $bag = $this->createBag(['foo' => 'bar']);

        $mutable = $bag->mutable();

        $this->assertNotSame($bag, $mutable);
        $this->assertInstanceOf(Bag::class, $mutable);
        $this->assertEquals(['foo' => 'bar'], $mutable->toArray());
    }

    public function testImmutable()
    {
        $bag = $this->createBag(['foo', 'bar']);

        $immutable = $bag->immutable();

        $this->assertNotSame($bag, $immutable);
        $this->assertInstanceOf(ImmutableBag::class, $immutable);
        $this->assertEquals(['foo', 'bar'], $immutable->toArray());
    }

    public function testKeys()
    {
        $bag = $this->createBag(['foo' => 'bar', 'hello' => 'world']);

        $keys = $bag->keys();

        $this->assertBagResult(['foo', 'hello'], $bag, $keys);
    }

    public function testValues()
    {
        $bag = $this->createBag(['foo' => 'bar', 'hello' => 'world']);

        $values = $bag->values();

        $this->assertBagResult(['bar', 'world'], $bag, $values);
    }

    public function testMap()
    {
        $bag = $this->createBag(['foo' => 'bar', 'hello' => 'world']);

        $actual = $bag->map(function ($key, $item) {
            return $key . '.' . $item;
        });

        $this->assertBagResult(['foo' => 'foo.bar', 'hello' => 'hello.world'], $bag, $actual);
    }

    public function testMapKeys()
    {
        $bag = $this->createBag(['foo' => 'bar', 'hello' => 'world']);

        $actual = $bag->mapKeys(function ($key, $item) {
            return $key . '.' . $item;
        });

        $this->assertBagResult(['foo.bar' => 'bar', 'hello.world' => 'world'], $bag, $actual);
    }

    public function testFilter()
    {
        $bag = $this->createBag(['foo', 'bar', 'hello', 'world']);

        $actual = $bag->filter(function ($key, $item) {
            return $item !== 'bar' && $key !== 2;
        });

        $this->assertBagResult([0 => 'foo', 3 => 'world'], $bag, $actual);
    }

    public function testClean()
    {
        $bag = $this->createBag([null, '', 'foo', false, 0, true, [], ['bar']]);

        $actual = $bag->clean();

        $this->assertBagResult([2 => 'foo', 5 => true, 7 => ['bar']], $bag, $actual);
    }

    public function testReplace()
    {
        $bag = $this->createBag(['foo' => 'bar']);

        $actual = $bag->replace(['foo' => 'baz', 'hello' => 'world']);

        $this->assertBagResult(['foo' => 'baz', 'hello' => 'world'], $bag, $actual);
    }

    /**
     * @dataProvider \Bolt\Collection\Tests\ArrTest::provideReplaceRecursive
     *
     * @param array $array1
     * @param array $array2
     * @param array $expected
     */
    public function testReplaceRecursive($array1, $array2, $expected)
    {
        $cls = $this->cls;
        $bag = $cls::from($array1);

        $actual = $bag->replaceRecursive($array2);

        $this->assertBagResult($expected, $bag, $actual);
    }

    public function testDefaults()
    {
        $bag = $this->createBag(['foo' => 'bar']);

        $actual = $bag->defaults(['foo' => 'baz', 'hello' => 'world']);

        $this->assertBagResult(['foo' => 'bar', 'hello' => 'world'], $bag, $actual);
    }

    /**
     * @dataProvider \Bolt\Collection\Tests\ArrTest::provideReplaceRecursive
     *
     * @param array $array1
     * @param array $array2
     * @param array $expected
     */
    public function testDefaultsRecursive($array1, $array2, $expected)
    {
        $cls = $this->cls;
        $bag = $cls::from($array2);

        $actual = $bag->defaultsRecursive($array1);

        $this->assertBagResult($expected, $bag, $actual);
    }

    public function testMerge()
    {
        $bag = $this->createBag(['foo', 'bar']);

        $actual = $bag->merge(['hello', 'world']);

        $this->assertBagResult(['foo', 'bar', 'hello', 'world'], $bag, $actual);
    }

    public function provideSlice()
    {
        return [
            [0,  null, false, ['foo', 'bar', 'hello', 'world']],
            [1,  null, false,        ['bar', 'hello', 'world']],
            [1,     2, false,        ['bar', 'hello']],
            [-2, null, false,               ['hello', 'world']],
            [1,    -1, false,        ['bar', 'hello']],
            [-2,   -1, false,               ['hello']],
            [1,     2,  true, [1 => 'bar', 2 => 'hello']],
        ];
    }

    /**
     * @dataProvider provideSlice
     *
     * @param int      $offset
     * @param int|null $length
     * @param bool     $preserveKeys
     * @param array    $expected
     */
    public function testSlice($offset, $length, $preserveKeys, $expected)
    {
        $bag = $this->createBag(['foo', 'bar', 'hello', 'world']);

        $actual = $bag->slice($offset, $length, $preserveKeys);

        $this->assertBagResult($expected, $bag, $actual);
    }

    public function testPartition()
    {
        $bag = $this->createBag(['foo' => 'bar', 'hello' => 'world']);

        $actual = $bag->partition(function ($key, $item) {
            return strpos($item, 'a') !== false;
        });

        $this->assertInternalType('array', $actual);
        $this->assertCount(2, $actual);

        list($trueBag, $falseBag) = $actual;
        $this->assertBagResult(['foo' => 'bar'], $bag, $trueBag);
        $this->assertBagResult(['hello' => 'world'], $bag, $falseBag);
    }

    public function testColumn()
    {
        $bag = $this->createBag([
            ['id' => 'foo', 'value' => 'bar'],
            ['id' => 'hello', 'value' => 'world'],
        ]);

        $actual = $bag->column('id');
        $this->assertBagResult(['foo', 'hello'], $bag, $actual);

        $actual = $bag->column('value', 'id');
        $this->assertBagResult(['foo' => 'bar', 'hello' => 'world'], $bag, $actual);
    }

    public function testFlip()
    {
        $bag = $this->createBag(['foo' => 'bar', 'hello' => 'world', 'second' => 'world']);

        $actual = $bag->flip();

        $this->assertBagResult(['bar' => 'foo', 'world' => 'second'], $bag, $actual);
    }

    public function testReduce()
    {
        $bag = $this->createBag([1, 2, 3, 4]);

        $product = $bag->reduce(
            function ($carry, $item) {
                return $carry * $item;
            },
            1
        );

        $this->assertEquals(24, $product);
    }

    public function testUnique()
    {
        $bag = $this->createBag(['foo', 'bar', 'foo', 3, '3', '3a', '3']);
        $actual = $bag->unique();
        $this->assertBagResult(['foo', 'bar', 3, '3', '3a'], $bag, $actual);

        $first = $this->createBag();
        $second = $this->createBag();
        $bag = $this->createBag([$first, $second, $first]);
        $actual = $bag->unique();
        $this->assertBagResult([$first, $second], $bag, $actual);
    }

    public function testChunk()
    {
        $bag = $this->createBag(['a', 'b', 'c', 'd', 'e']);

        $chunked = $bag->chunk(2);

        $this->assertInstanceOf($this->cls, $chunked);
        $this->assertNotSame($bag, $chunked);
        $this->assertCount(3, $chunked);

        $this->assertBagResult(['a', 'b'], $bag, $chunked->get(0));
        $this->assertBagResult(['c', 'd'], $bag, $chunked->get(1));
        $this->assertBagResult(['e'], $bag, $chunked->get(2));
    }

    public function testChunkPreserveKeys()
    {
        $bag = $this->createBag(['a', 'b', 'c', 'd', 'e']);

        $chunked = $bag->chunk(2, true);

        $this->assertInstanceOf($this->cls, $chunked);
        $this->assertNotSame($bag, $chunked);
        $this->assertCount(3, $chunked);

        $this->assertBagResult(['a', 'b'], $bag, $chunked->get(0));
        $this->assertBagResult([2 => 'c', 3 => 'd'], $bag, $chunked->get(1));
        $this->assertBagResult([4 => 'e'], $bag, $chunked->get(2));
    }

    // endregion

    // region Sorting Methods

    public function testReverse()
    {
        $bag = $this->createBag(['a', 'b', 'c', 'd']);

        $actual = $bag->reverse();

        $this->assertBagResult(['d', 'c', 'b', 'a'], $bag, $actual);
    }

    public function testReversePreserveKeys()
    {
        $bag = $this->createBag(['a', 'b', 'c', 'd']);

        $actual = $bag->reverse(true);

        $this->assertBagResult([3 => 'd', 2 => 'c', 1 => 'b', 0 => 'a'], $bag, $actual);
    }

    public function testShuffle()
    {
        $bag = $this->createBag(['a', 'b', 'c', 'd']);

        $actual = $bag->shuffle();

        $this->assertInstanceOf($this->cls, $actual);
        $this->assertNotSame($bag, $actual);

        $sorted = $actual->toArray();
        sort($sorted);
        $this->assertEquals($bag->toArray(), $sorted);

        // reduce odds that shuffle is produces same order.
        for ($i = 0; $i < 10; ++$i) {
            if ($bag->toArray() !== $actual->toArray()) {
                break;
            }
            $actual = $bag->shuffle();
        }

        $this->assertNotEquals($bag->toArray(), $actual->toArray());
    }

    // endregion

    // region Internal Methods

    public function testIterator()
    {
        $bag = $this->createBag(['a', 'b', 'c', 'd']);

        $this->assertEquals(['a', 'b', 'c', 'd'], iterator_to_array($bag));
    }

    public function testJsonSerializable()
    {
        $bag = $this->createBag(['a', 'b', 'c']);

        $this->assertEquals('["a","b","c"]', json_encode($bag));
    }

    public function testOffsetExists()
    {
        $arr = ['foo' => 'bar', 'null' => null];
        $bag = $this->createBag($arr);

        $this->assertTrue(isset($bag['foo']));
        $this->assertTrue(isset($bag['null'])); // doesn't have PHPs stupid edge case.
        $this->assertFalse(isset($bag['derp']));

        $this->assertFalse(isset($arr['null'])); // just why PHP, why!
    }

    public function testOffsetGet()
    {
        $bag = $this->createBag(['foo' => 'bar']);

        $this->assertEquals('bar', $bag['foo']);
        $this->assertNull($bag['nope']);
    }

    public function testOffsetGetByReference()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('HHVM does not trigger indirect modification notice.');
            // we also don't know if it's being asked for by reference to throw an exception.
        }

        $bag = $this->createBag(['arr' => ['1']]);

        // Assert arrays are not able to be modified by reference.
        $errors = new \ArrayObject();
        set_error_handler(function ($type, $message) use ($errors) {
            $errors[] = [$type, $message];
        });

        $arr = &$bag['arr'];

        restore_error_handler();

        $this->assertEquals([[E_NOTICE, 'Indirect modification of overloaded element of Bolt\Collection\ImmutableBag has no effect']], $errors->getArrayCopy());
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage Cannot modify items on an Bolt\Collection\ImmutableBag
     */
    public function testOffsetSet()
    {
        $bag = $this->createBag();

        $bag['foo'] = 'bar';
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage Cannot remove items from an Bolt\Collection\ImmutableBag
     */
    public function testOffsetUnset()
    {
        $bag = $this->createBag(['foo' => 'bar']);

        unset($bag['foo']);
    }

    public function testDebugInfo()
    {
        $bag = $this->createBag(['foo' => 'bar']);

        $this->assertEquals($bag->toArray(), $bag->__debugInfo());
        $this->assertEquals('bar', $bag->foo);
    }

    // endregion

    /**
     * Assert $actualBag is an ImmutableBag that's a different instance from $initialBag and its items equal $expected.
     *
     * @param array        $expected
     * @param ImmutableBag $initialBag
     * @param ImmutableBag $actualBag
     */
    protected function assertBagResult($expected, $initialBag, $actualBag)
    {
        $this->assertInstanceOf($this->cls, $actualBag);
        $this->assertNotSame($initialBag, $actualBag);
        $this->assertEquals($expected, $actualBag->toArray());
    }
}
