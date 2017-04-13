<?php

namespace Bolt\Collection\Tests;

use Bolt\Collection\Arr;
use PHPUnit\Framework\TestCase;

/**
 * Tests for \Bolt\Collection\Arr
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class ArrTest extends TestCase
{
    public function testColumn()
    {
        $data = [
            new TestColumn('foo', 'bar'),
            new TestColumn('hello', 'world'),
            ['id' => '5', 'value' => 'asdf'],
        ];

        $result = Arr::column($data, 'id');
        $this->assertEquals(['foo', 'hello', '5'], $result);

        $result = Arr::column($data, 'value', 'id');
        $expected = [
            'foo'   => 'bar',
            'hello' => 'world',
            '5'     => 'asdf',
        ];
        $this->assertEquals($expected, $result);
    }

    public function provideGetSetHasInvalidArgs()
    {
        return [
            'data not accessible' => [new \EmptyIterator(), 'foo'],
            'path not string' => [[], false],
            'empty path' => [[], ''],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider provideGetSetHasInvalidArgs
     *
     * @param mixed $data
     * @param mixed $path
     */
    public function testHasInvalidArgs($data, $path)
    {
        Arr::has($data, $path);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider provideGetSetHasInvalidArgs
     *
     * @param mixed $data
     * @param mixed $path
     */
    public function testGetInvalidArgs($data, $path)
    {
        Arr::get($data, $path);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider provideGetSetHasInvalidArgs
     *
     * @param mixed $data
     * @param mixed $path
     */
    public function testSetInvalidArgs($data, $path)
    {
        Arr::set($data, $path, 'mixed');
    }

    public function provideGetSetHas()
    {
        return [
            'array' => [[
                'foo' => 'bar',
                'items' => [
                    'nested' => [
                        'hello' => 'world',
                    ],
                    'obj' => new \EmptyIterator(),
                ],
            ]],

            'array access' => [new \ArrayObject([
                'foo' => 'bar',
                'items' => new \ArrayObject([
                    'nested' => new \ArrayObject([
                        'hello' => 'world',
                    ]),
                    'obj' => new \EmptyIterator(),
                ]),
            ])],

            'user array access' => [new TestArrayLike([
                'foo' => 'bar',
                'items' => new TestArrayLike([
                    'nested' => new TestArrayLike([
                        'hello' => 'world',
                    ]),
                    'obj' => new \EmptyIterator(),
                ]),
            ])],

            'mixed' => [[
                'foo' => 'bar',
                'items' => new \ArrayObject([
                    'nested' => [
                        'hello' => 'world',
                    ],
                    'obj' => new \EmptyIterator(),
                ]),
            ]],
        ];
    }

    /**
     * @dataProvider provideGetSetHas
     *
     * @param array|\ArrayAccess $data
     */
    public function testHas($data)
    {
        $this->assertTrue(Arr::has($data, 'foo'));
        $this->assertTrue(Arr::has($data, 'items'));
        $this->assertTrue(Arr::has($data, 'items/nested/hello'));

        $this->assertFalse(Arr::has($data, 'derp'));
        $this->assertFalse(Arr::has($data, 'items/obj/bad'));
    }

    /**
     * @dataProvider provideGetSetHas
     *
     * @param array|\ArrayAccess $data
     */
    public function testGet($data)
    {
        $this->assertEquals('bar', Arr::get($data, 'foo'));
        $this->assertEquals('world', Arr::get($data, 'items/nested/hello'));

        $this->assertEquals('default', Arr::get($data, 'derp', 'default'));
        $this->assertEquals('default', Arr::get($data, 'items/derp', 'default'));
        $this->assertEquals('default', Arr::get($data, 'derp/nope/whoops', 'default'));
    }

    /**
     * @dataProvider provideGetSetHas
     *
     * @param array|\ArrayAccess $data
     */
    public function testSet($data)
    {
        Arr::set($data, 'color', 'red');
        $this->assertEquals('red', $data['color']);

        Arr::set($data, '[]', 'first');
        $this->assertEquals('first', $data[0]);
        Arr::set($data, '[]', 'second');
        $this->assertEquals('second', $data[1]);

        Arr::set($data, 'items/nested/color', 'blue');
        $this->assertEquals('blue', $data['items']['nested']['color']);

        Arr::set($data, 'items/nested/new/point', 'bolt');
        $this->assertEquals('bolt', $data['items']['nested']['new']['point']);

        Arr::set($data, 'items/nested/list/[]', 'first');
        $this->assertEquals('first', $data['items']['nested']['list'][0]);
        Arr::set($data, 'items/nested/list/[]', 'second');
        $this->assertEquals('second', $data['items']['nested']['list'][1]);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Cannot set "a/foo", because "a" is already set and not an array or an object implementing ArrayAccess.
     */
    public function testSetNestedInaccessibleObject()
    {
        $data = [
            'a' => new \EmptyIterator(),
        ];

        Arr::set($data, 'a/foo', 'bar');
    }

    /**
     * Test that Arr::set throws exception when trying to indirectly modify an ArrayAccess object.
     * This happens when one tries to set a value on a sub array/object of an AA object.
     * Ex: A/B/C where A is an object. B can be anything.
     */
    public function testSetNestedIndirectModificationError()
    {
        $data = [
            'a' => new TestBadArrayLike(),
        ];

        $prevReporting = error_reporting(E_ALL);
        $expectedErrorHandler = set_error_handler('var_dump');
        restore_error_handler();

        $errors = new \ArrayObject();
        set_error_handler(function ($type, $message) use ($errors) {
            $errors[] = [$type, $message];
        });

        $e = null;
        try {
            Arr::set($data, 'a/foo/bar', 'baz');
        } catch (\Exception $e) {
        } catch (\Throwable $e) {
        }
        error_reporting($prevReporting);
        restore_error_handler();

        $actualErrorHandler = set_error_handler('var_dump');
        restore_error_handler();

        $message = 'Arr::set did not restore previous error handler';
        if (is_array($expectedErrorHandler)) {
            $this->assertTrue(is_array($actualErrorHandler), $message);
            $this->assertSame($expectedErrorHandler[0], $actualErrorHandler[0], $message);
            $this->assertSame($expectedErrorHandler[1], $actualErrorHandler[1], $message);
        } else {
            $this->assertSame($expectedErrorHandler, $actualErrorHandler, $message);
        }

        $this->assertEquals([[E_USER_NOTICE, 'Some notice']], $errors->getArrayCopy(), 'Arr::set did not call previous error handler for non indirect modification errors');

        if ($e instanceof \RuntimeException) {
            $this->assertEquals('Cannot to set "a/foo/bar", because "a" is an Bolt\Collection\Tests\TestBadArrayLike which has not defined its offsetGet() method as return by reference.', $e->getMessage());
        } else {
            $this->fail("Arr::set should've thrown a RuntimeException");
        }
    }

    public function testIsAccessible()
    {
        $this->assertTrue(Arr::isAccessible([]));
        $this->assertTrue(Arr::isAccessible(new \ArrayObject()));

        $this->assertFalse(Arr::isAccessible(new \EmptyIterator()));
    }

    public function testAssertAccessible()
    {
        $e = null;

        try {
            Arr::assertAccessible([]);
            Arr::assertAccessible(new \ArrayObject());
        } catch (\Exception $e) {
        }

        $this->assertNull($e);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected an array or an object implementing ArrayAccess. Got: EmptyIterator
     */
    public function testAssertAccessibleFail()
    {
        Arr::assertAccessible(new \EmptyIterator());
    }

    public function provideIsIndexed()
    {
        return [
            'key value pairs'                  => [['key' => 'value'], false],
            'empty array'                      => [[], true],
            'list'                             => [['foo', 'bar'], true],
            'zero-indexed numeric int keys'    => [[0 => 'foo', 1 => 'bar'], true],
            'zero-indexed numeric string keys' => [['0' => 'foo', '1' => 'bar'], true],
            'non-zero-indexed keys'            => [[1 => 'foo', 2 => 'bar'], false],
            'non-sequential keys'              => [[0 => 'foo', 2 => 'bar'], false],
        ];
    }

    /**
     * @dataProvider provideIsIndexed
     *
     * @param array $array
     * @param bool  $indexed
     */
    public function testIsIndexedAndAssociative($array, $indexed)
    {
        $this->assertEquals($indexed, Arr::isIndexed($array));
        $this->assertEquals(!$indexed, Arr::isAssociative($array));

        $traversable = new \ArrayObject($array);
        $this->assertEquals($indexed, Arr::isIndexed($traversable));
        $this->assertEquals(!$indexed, Arr::isAssociative($traversable));
    }

    public function testNonArraysAreNotIndexedOrAssociative()
    {
        $this->assertFalse(Arr::isIndexed('derp'));
        $this->assertFalse(Arr::isAssociative('derp'));
    }

    public function provideReplaceRecursive()
    {
        return [
            'scalar replaces scalar (no duh)'         => [
                ['a' => ['b' => 'foo']],
                ['a' => ['b' => 'bar']],
                ['a' => ['b' => 'bar']],
            ],
            'second adds to first (no duh)'           => [
                ['a' => ['b' => 'foo']],
                ['a' => ['c' => 'bar']],
                ['a' => ['b' => 'foo', 'c' => 'bar']],
            ],
            'list replaces list completely'           => [
                ['a' => ['foo', 'bar']],
                ['a' => ['baz']],
                ['a' => ['baz']],
            ],
            'null replaces scalar'                    => [
                ['a' => ['b' => 'foo']],
                ['a' => ['b' => null]],
                ['a' => ['b' => null]],
            ],
            'null ignores arrays (both types)'        => [
                ['a' => ['b' => 'foo']],
                ['a' => null],
                ['a' => ['b' => 'foo']],
            ],
            'empty list replaces arrays (both types)' => [
                ['a' => ['foo', 'bar']],
                ['a' => []],
                ['a' => []],
            ],
            'scalar replaces arrays (both types)'     => [
                ['a' => ['foo', 'bar']],
                ['a' => 'derp'],
                ['a' => 'derp'],
            ],
        ];
    }

    /**
     * @dataProvider provideReplaceRecursive
     *
     * @param array $array1
     * @param array $array2
     * @param array $result
     */
    public function testReplaceRecursive($array1, $array2, $result)
    {
        $this->assertEquals($result, Arr::replaceRecursive($array1, $array2));
    }
}

class TestArrayLike implements \ArrayAccess
{
    protected $items = [];

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function offsetExists($offset)
    {
        @trigger_error('Some notice', E_USER_NOTICE);

        return isset($this->items[$offset]);
    }

    public function &offsetGet($offset) // Note "&"
    {
        return $this->items[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }
}

class TestBadArrayLike extends TestArrayLike
{
    public function offsetGet($offset) // Note no "&"
    {
        return $this->items[$offset];
    }
}

class TestColumn
{
    public $id;
    private $value;

    public function __construct($id, $value)
    {
        $this->id = $id;
        $this->value = $value;
    }

    public function __isset($name)
    {
        return $name === 'value';
    }

    public function __get($name)
    {
        return $this->value;
    }
}