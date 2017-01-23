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
