<?php

namespace Bolt\Collection;

use ArrayAccess;
use Bolt\Common\Assert;
use Bolt\Common\Deprecated;
use Bolt\Common\Thrower;
use Countable;
use ErrorException;
use InvalidArgumentException;
use IteratorAggregate;
use JsonSerializable;
use LogicException;
use stdClass;

/**
 * An OO implementation of PHP's array functionality and more (minus mutability).
 *
 * All methods that allow mutation are deprecated, use {@see MutableBag} for those use cases instead.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class Bag implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    /** @var array */
    protected $items;

    // region Creation / Unwrapping Methods

    /**
     * Constructor.
     *
     * @param array $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * Creates a list from the arguments given.
     *
     * @param ...mixed $items
     *
     * @return static
     */
    public static function of()
    {
        return new static(func_get_args());
    }

    /**
     * Create a bag from a variety of collections.
     *
     * @param iterable|stdClass|null $collection
     *
     * @return static
     */
    public static function from($collection)
    {
        return new static(Arr::from($collection));
    }

    /**
     * Takes the items and recursively converts them to Bags.
     *
     * @param iterable|stdClass|null $collection
     *
     * @return static
     */
    public static function fromRecursive($collection)
    {
        $arr = Arr::from($collection);

        foreach ($arr as $key => $value) {
            if ($value instanceof stdClass || \is_iterable($value)) {
                $value = static::fromRecursive($value);
            }
            $arr[$key] = $value;
        }

        return new static($arr);
    }

    /**
     * Creates a bag by using one collection for keys and another for its values.
     *
     * @param iterable $keys
     * @param iterable $values
     *
     * @return static
     */
    public static function combine($keys, $values)
    {
        $keys = Arr::from($keys);
        $values = Arr::from($values);

        if (count($keys) !== count($values)) {
            throw new InvalidArgumentException('The size of keys and values needs to be the same.');
        }

        if (count($keys) === 0) {
            return new static();
        }

        return new static(array_combine($keys, $values));
    }

    /**
     * Returns the array of items.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->items;
    }

    /**
     * Returns the items recursively converting them to arrays.
     *
     * @return array
     */
    public function toArrayRecursive()
    {
        return Arr::fromRecursive($this->items);
    }

    /**
     * Creates a new instance from the specified items.
     *
     * This method is provided for derived classes to specify how a new
     * instance should be created when constructor semantics have changed.
     *
     * @param array $items
     *
     * @return static
     */
    protected function createFrom(array $items)
    {
        return new static($items);
    }

    // endregion

    // region Methods returning a single value

    /**
     * Returns whether an item exists for the given key.
     *
     * @param string $key The key
     *
     * @return bool
     */
    public function has($key)
    {
        return isset($this->items[$key]) || array_key_exists($key, $this->items);
    }

    /**
     * Returns whether a key exists using path syntax to check nested data.
     *
     * Example:
     *
     *     $bag->hasPath('foo/bar/baz')
     *     // => true
     *
     * This method does not allow for keys that contain `/`.
     *
     * @param string $path The path to traverse and check keys from
     *
     * @return bool
     */
    public function hasPath($path)
    {
        return Arr::has($this->items, $path);
    }

    /**
     * Returns whether the item is in the bag.
     *
     * This uses a strict check so types must much and objects must be the same instance to match.
     *
     * @param mixed $item
     *
     * @return bool
     */
    public function hasItem($item)
    {
        return in_array($item, $this->items, true);
    }

    /**
     * Returns an item by its key.
     *
     * @param string $key     The key
     * @param mixed  $default The default value if the key does not exist
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return (isset($this->items[$key]) || array_key_exists($key, $this->items)) ? $this->items[$key] : $default;
    }

    /**
     * Returns an item using path syntax to retrieve nested data.
     *
     * Example:
     *
     *     // Get the bar key of a set of nested arrays.
     *     // This is equivalent to $data['foo']['baz']['bar'] but won't
     *     // throw warnings for missing keys.
     *     $bag->getPath('foo/bar/baz');
     *
     * This method does not allow for keys that contain `/`.
     *
     * @param string $path    The path to traverse and retrieve an item from
     * @param mixed  $default The default value if the key does not exist
     *
     * @return mixed
     */
    public function getPath($path, $default = null)
    {
        return Arr::get($this->items, $path, $default);
    }

    /**
     * Returns the number of items in this bag.
     *
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * Checks whether the bag is empty.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return !$this->items;
    }

    /**
     * Returns the first item in the list or null if empty.
     *
     * @return mixed|null
     */
    public function first()
    {
        return $this->items ? reset($this->items) : null;
    }

    /**
     * Returns the last item in the list or null if empty.
     *
     * @return mixed|null
     */
    public function last()
    {
        return $this->items ? end($this->items) : null;
    }

    /**
     * Joins the list to a string.
     *
     * @param string $separator The term to join on
     *
     * @return string A string representation of all the items with the separator between them
     */
    public function join($separator)
    {
        return implode($separator, $this->items);
    }

    /**
     * Returns the sum of the values in this list.
     *
     * @return number
     */
    public function sum()
    {
        return array_sum($this->items);
    }

    /**
     * Returns the product of the values in this list.
     *
     * @return number
     */
    public function product()
    {
        return array_product($this->items);
    }

    /**
     * Returns whether the items in this bag are key/value pairs.
     *
     * Note: Empty bags are not.
     *
     * @return bool
     */
    public function isAssociative()
    {
        return Arr::isAssociative($this->items);
    }

    /**
     * Returns whether the items in this bag are zero indexed and sequential.
     *
     * Note: Empty bags are.
     *
     * @return bool
     */
    public function isIndexed()
    {
        return !$this->isAssociative();
    }

    /**
     * Gets the first index/key of a given item.
     *
     * This uses a strict check so types must much and objects must be the same instance to match.
     *
     * @param mixed $item      The item to search for
     * @param int   $fromIndex The starting index to search from.
     *                         Can be negative to start from that far from the end of the array.
     *                         If index is out of bounds, it will be moved to first/last index.
     *
     * @return int|string|null The index or key of the item or null if the item was not found
     */
    public function indexOf($item, $fromIndex = 0)
    {
        foreach ($this->iterateFromIndex($fromIndex) as $key => $value) {
            if ($value === $item) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Gets the last index/key of a given item.
     *
     * This uses a strict check so types must much and objects must be the same instance to match.
     *
     * @param mixed $item      The item to search for
     * @param int   $fromIndex The starting index to search from. Default is the last index.
     *                         Can be negative to start from that far from the end of the array.
     *                         If index is out of bounds, it will be moved to first/last index.
     *
     * @return int|string|null The index or key of the item or null if the item was not found
     */
    public function lastIndexOf($item, $fromIndex = null)
    {
        foreach ($this->iterateReverseFromIndex($fromIndex) as $key => $value) {
            if ($value === $item) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Returns the first item that matches the `$predicate` or null.
     *
     * @param callable $predicate Function is passed `($value, $key)`
     * @param int      $fromIndex The starting index to search from.
     *                            Can be negative to start from that far from the end of the array.
     *                            If index is out of bounds, it will be moved to first/last index.
     *
     * @return mixed|null
     */
    public function find(callable $predicate, $fromIndex = 0)
    {
        $index = $this->findKey($predicate, $fromIndex);

        return $index !== null ? $this->items[$index] : null;
    }

    /**
     * Returns the last item that matches the `$predicate` or null.
     *
     * @param callable $predicate Function is passed `($value, $key)`
     * @param int      $fromIndex The starting index to search from.
     *                            Can be negative to start from that far from the end of the array.
     *                            If index is out of bounds, it will be moved to first/last index.
     *
     * @return mixed|null
     */
    public function findLast(callable $predicate, $fromIndex = null)
    {
        $index = $this->findLastKey($predicate, $fromIndex);

        return $index !== null ? $this->items[$index] : null;
    }

    /**
     * Returns the first key that matches the `$predicate` or null.
     *
     * @param callable $predicate Function is passed `($value, $key)`
     * @param int      $fromIndex The starting index to search from.
     *                            Can be negative to start from that far from the end of the array.
     *                            If index is out of bounds, it will be moved to first/last index.
     *
     * @return mixed|null
     */
    public function findKey(callable $predicate, $fromIndex = 0)
    {
        foreach ($this->iterateFromIndex($fromIndex) as $key => $value) {
            if ($predicate($value, $key)) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Returns the last key that matches the `$predicate` or null.
     *
     * @param callable $predicate Function is passed `($value, $key)`
     * @param int      $fromIndex The starting index to search from.
     *                            Can be negative to start from that far from the end of the array.
     *                            If index is out of bounds, it will be moved to first/last index.
     *
     * @return mixed|null
     */
    public function findLastKey(callable $predicate, $fromIndex = null)
    {
        foreach ($this->iterateReverseFromIndex($fromIndex) as $key => $value) {
            if ($predicate($value, $key)) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Iterate through the items starting at the given index.
     *
     * @param int $fromIndex The starting index to search from.
     *                       Can be negative to start from that far from the end of the array.
     *                       If index is out of bounds, it will be moved to first/last index.
     *
     * @return \Generator
     */
    private function iterateFromIndex($fromIndex)
    {
        Assert::integer($fromIndex);

        $count = count($this->items);

        if ($count === 0) {
            return;
        }

        $last = $count - 2;

        $index = $fromIndex < 0 ? max($last + $fromIndex, -1) : min($fromIndex - 1, $last);

        $keys = array_keys($this->items);

        while (++$index < $count) {
            $key = $keys[$index];
            yield $key => $this->items[$key];
        }
    }

    /**
     * Reverse iterate through the items starting at the given index.
     *
     * @param int $fromIndex The starting index to search from. Default is the last index.
     *                       Can be negative to start from that far from the end of the array.
     *                       If index is out of bounds, it will be moved to first/last index.
     *
     * @return \Generator
     */
    private function iterateReverseFromIndex($fromIndex)
    {
        Assert::nullOrInteger($fromIndex);

        $index = count($this->items);

        if ($index === 0) {
            return;
        }

        if ($fromIndex !== null) {
            $index = $fromIndex < 0 ? max($index + $fromIndex, 1) : min($fromIndex + 1, $index);
        }

        $keys = array_keys($this->items);

        while (--$index >= 0) {
            $key = $keys[$index];
            yield $key => $this->items[$key];
        }
    }

    /**
     * Returns a random value.
     *
     * @throws \InvalidArgumentException when the bag is empty
     *
     * @return mixed
     */
    public function randomValue()
    {
        return $this->randomValues(1)->first();
    }

    /**
     * Returns a random key.
     *
     * @throws \InvalidArgumentException when the bag is empty
     *
     * @return mixed
     */
    public function randomKey()
    {
        return $this->randomKeys(1)->first();
    }

    // endregion

    // region Methods returning a new bag

    /**
     * Calls the `$callable` to modify the items.
     *
     * This allows for chain-ability with custom functionality.
     *
     * The `$callable` is given the bag's items (array) as the first parameter and should return an iterable which is
     * then converted to a bag. Any extra parameters passed in to this method are passed to the `$callable` after
     * the items parameter.
     *
     * <br>
     * Example with closure:
     *
     *     Bag::from(['red', 'blue'])
     *         ->call(function (array $colors) {
     *             $colors[] = 'green';
     *
     *             return $colors;
     *         })
     *         ->join(', ');
     *     // => "red, blue, green"
     *
     * <br>
     * Example with function name and args:
     *
     *     Bag::from(['red', 'blue'])
     *         ->call('array_pad', 4, ''); // Assuming bag doesn't have a pad method ;)
     *     // => Bag of ['red', 'blue', '', '']
     *
     * @param callable $callable Function is given `($items, ...$args)` and should return an iterable
     * @param array    ...$args  Extra parameters to pass to the `$callable` after the items parameter
     *
     * @return static
     */
    public function call(callable $callable, /*...*/$args = null)
    {
        // Optimized for no args. Argument unpacking is still faster once we get to use 5.6 syntax
        $result = $args ? call_user_func_array($callable, [$this->items] + func_get_args()) : $callable($this->items);
        // $result = $callable($this->items, ...$args);

        return $this->createFrom(Arr::from($result));
    }

    /**
     * Returns a mutable bag with the items from this bag.
     *
     * @return MutableBag
     */
    public function mutable()
    {
        return new MutableBag($this->items);
    }

    /**
     * Returns an immutable bag with the items from this bag.
     *
     * @return Bag
     */
    public function immutable()
    {
        return new self($this->items);
    }

    /**
     * Returns a bag with all the keys of the items.
     *
     * @return static
     */
    public function keys()
    {
        return $this->createFrom(array_keys($this->items));
    }

    /**
     * Returns a bag with all the values of the items.
     *
     * Useful for reindexing a list.
     *
     * @return static
     */
    public function values()
    {
        return $this->createFrom(array_values($this->items));
    }

    /**
     * Applies the `$callable` to each _value_ in the bag and returns
     * a new bag with the items returned by the function.
     *
     * Note: This differs from {@see array_map} in that the callback is passed `$key` first, then `$value`.
     *
     * @param callable $callback Function is passed `($key, $value)`
     *
     * @return static
     */
    public function map(callable $callback)
    {
        $items = [];

        foreach ($this->items as $key => $value) {
            $items[$key] = $callback($key, $value);
        }

        return $this->createFrom($items);
    }

    /**
     * Applies the given function to each _key_ in the bag and returns
     * a new bag with the keys returned by the function and their values.
     *
     * @param callable $callback Function is passed `($key, $value)`
     *
     * @return static
     */
    public function mapKeys(callable $callback)
    {
        $items = [];

        foreach ($this->items as $key => $value) {
            $items[$callback($key, $value)] = $value;
        }

        return $this->createFrom($items);
    }

    /**
     * Returns a bag with the items that satisfy the `$predicate`.
     *
     * Keys are preserved, so lists could need to be re-indexed.
     *
     * Note: This differs from {@see array_filter} in that the `$predicate` is passed `$key` first, then `$value`.
     *
     * @param callable $predicate Function is passed `($key, $value)`
     *
     * @return static
     */
    public function filter(callable $predicate)
    {
        $items = [];

        foreach ($this->items as $key => $value) {
            if ($predicate($key, $value)) {
                $items[$key] = $value;
            }
        }

        return $this->createFrom($items);
    }

    /**
     * Returns a bag with the items that do not satisfy the `$predicate`. The opposite of {@see filter}.
     *
     * Keys are preserved, so lists could need to be re-indexed.
     *
     * @param callable $predicate Function is passed `($key, $value)`
     *
     * @return static
     */
    public function reject(callable $predicate)
    {
        $items = [];

        foreach ($this->items as $key => $value) {
            if (!$predicate($key, $value)) {
                $items[$key] = $value;
            }
        }

        return $this->createFrom($items);
    }

    /**
     * Returns a bag with falsely values filtered out.
     *
     * @return static
     */
    public function clean()
    {
        return $this->createFrom(array_filter($this->items));
    }

    /**
     * Replaces items in this bag from the `$collection` by comparing keys and returns the result.
     *
     * @param iterable $collection The collection from which items will be extracted
     *
     * @return static
     */
    public function replace($collection)
    {
        return $this->createFrom(array_replace($this->items, Arr::from($collection)));
    }

    /**
     * Returns a bag with the items replaced recursively from the `$collection`.
     *
     * This differs from {@see array_replace_recursive} in a couple ways:
     *
     *  - Lists (zero indexed and sequential items) from given collection completely replace lists in this Bag.
     *
     *  - Null values from given collection do not replace lists or associative arrays in this Bag
     *    (they do still replace scalar values).
     *
     * @param iterable $collection The collection from which items will be extracted
     *
     * @return static
     */
    public function replaceRecursive($collection)
    {
        return $this->createFrom(Arr::replaceRecursive($this->items, Arr::from($collection)));
    }

    /**
     * Returns a bag with the items from the `$collection` added to the items in this bag
     * if they do not already exist by comparing keys. The opposite of {@see replace}.
     *
     * Example:
     *
     *     Bag::from(['foo' => 'bar'])
     *         ->defaults(['foo' => 'other', 'hello' => 'world']);
     *     // => Bag of ['foo' => 'bar', 'hello' => 'world']
     *
     * @param iterable $collection The collection from which items will be extracted
     *
     * @return static
     */
    public function defaults($collection)
    {
        return $this->createFrom(array_replace(Arr::from($collection), $this->items));
    }

    /**
     * Returns a bag with the items from the `$collection` recursively added to the items in this bag
     * if they do not already exist by comparing keys. The opposite of {@see replaceRecursive}.
     *
     * @param iterable $collection The collection from which items will be extracted
     *
     * @return static
     */
    public function defaultsRecursive($collection)
    {
        return $this->createFrom(Arr::replaceRecursive(Arr::from($collection), $this->items));
    }

    /**
     * Returns a bag with the items merged with the given list.
     *
     * Note: This should only be used for lists (zero indexed and sequential items).
     * For associative arrays, use {@see replace} instead.
     *
     * @param iterable $list The list of items to merge
     *
     * @return static
     */
    public function merge($list)
    {
        return $this->createFrom(array_merge($this->items, Arr::from($list)));
    }

    /**
     * Returns a bag with a slice of `$length` items starting at position `$offset` extracted from this bag.
     *
     * @param int      $offset       If positive, the offset to start from.
     *                               If negative, the bag will start that far from the end of the list.
     * @param int|null $length       If positive, the maximum number of items to return.
     *                               If negative, the bag will stop that far from the end of the list.
     *                               If null, the bag will have everything from the $offset to the end of the list.
     * @param bool     $preserveKeys Whether to preserve keys in the resulting bag or not
     *
     * @return static
     */
    public function slice($offset, $length = null, $preserveKeys = false)
    {
        return $this->createFrom(array_slice($this->items, $offset, $length, $preserveKeys));
    }

    /**
     * Partitions the items into two bags according to the `$predicate`.
     * Keys are preserved in the resulting bags.
     *
     * Example:
     *
     *     [$trueItems, $falseItems] = $bag->partition(function ($key, $item) {
     *         return true; // whatever logic
     *     });
     *
     * @param callable $predicate Function is passed `($key, $value)` and should return a `boolean`
     *
     * @return static[] [true bag, false bag]
     */
    public function partition(callable $predicate)
    {
        $coll1 = $coll2 = [];

        foreach ($this->items as $key => $item) {
            if ($predicate($key, $item)) {
                $coll1[$key] = $item;
            } else {
                $coll2[$key] = $item;
            }
        }

        return [$this->createFrom($coll1), $this->createFrom($coll2)];
    }

    /**
     * Returns a bag with the values from a single column, identified by the `$columnKey`.
     *
     * Optionally, an `$indexKey` may be provided to index the values in the
     * returned Bag by the values from the `$indexKey` column.
     *
     * Example:
     *
     *     $bag = Bag::from([
     *         ['id' => 10, 'name' => 'Alice'],
     *         ['id' => 20, 'name' => 'Bob'],
     *         ['id' => 30, 'name' => 'Carson'],
     *     ]);
     *
     *     $bag->column('name');
     *     // => Bag of ['Alice', 'Bob', 'Carson']
     *
     *     $bag->column('name', 'id');
     *     // => Bag of [10 => 'Alice', 20 => 'Bob', 30 => 'Carson']
     *
     * @param string|int|null $columnKey The key of the values to return or `null` for no change
     * @param string|int|null $indexKey  The key of the keys to return or `null` for no change
     *
     * @return static
     */
    public function column($columnKey, $indexKey = null)
    {
        return $this->createFrom(Arr::column($this->items, $columnKey, $indexKey));
    }

    /**
     * Returns a bag with all keys exchanged with their associated values.
     *
     * If a value has several occurrences, the latest key will be used as its value, and all others will be lost.
     *
     * @throws LogicException when values are not strings or integers
     *
     * @return static
     */
    public function flip()
    {
        if (!$this->items) {
            return $this->createFrom([]);
        }
        try {
            return $this->createFrom(Thrower::call('array_flip', $this->items));
        } catch (ErrorException $e) {
            throw new LogicException('Only string and integer values can be flipped');
        }
    }

    /**
     * Iteratively reduce the items to a single value using the `$callback` function.
     *
     * @param callable $callback Function is passed `$carry` (previous or initial value)
     *                           and `$item` (value of the current iteration)
     * @param mixed    $initial  Initial value
     *
     * @return mixed The resulting value or the initial value if list is empty
     */
    public function reduce(callable $callback, $initial = null)
    {
        return array_reduce($this->items, $callback, $initial);
    }

    /**
     * Returns a bag with duplicate values removed.
     *
     * @return static
     */
    public function unique()
    {
        $items = [];

        foreach ($this->items as $item) {
            if (array_search($item, $items, true) === false) {
                $items[] = $item;
            }
        }

        return $this->createFrom($items);
    }

    /**
     * Returns a bag with the items split into chunks.
     *
     * The last chunk may contain less items.
     *
     * Example:
     *
     *     Bag::from([1, 2, 3, 4, 5])
     *         ->chunk(2);
     *     // => Bag of [Bag of [1, 2], Bag of [3, 4], Bag of [5]]
     *
     * @param int  $size         The size of each chunk
     * @param bool $preserveKeys When set to TRUE keys will be preserved. Default is FALSE which will reindex
     *                           the chunk numerically.
     *
     * @return static|static[] Returns a multidimensional bag, with each dimension containing $size items
     */
    public function chunk($size, $preserveKeys = false)
    {
        $create = function ($items) {
            return $this->createFrom($items);
        };

        return $this->createFrom(array_map($create, array_chunk($this->items, $size, $preserveKeys)));
    }

    /**
     * Returns a bag with the items padded to the `$size` with the `$value`.
     *
     * If size is positive then the array is padded on the right.
     * If it's negative then on the left.
     *
     * Examples:
     *
     *     $bag = Bag::from([1, 2]);
     *
     *     $bag->pad(4, null);
     *     // => Bag of [1, 2, null, null]
     *
     *     $bag->pad(-4, null);
     *     // => Bag of [null, null, 1, 2]
     *
     *     $bag->pad(2, null);
     *     // => Bag of [1, 2]
     *
     * @param int   $size
     * @param mixed $value
     *
     * @return static
     */
    public function pad($size, $value)
    {
        return $this->createFrom(array_pad($this->items, $size, $value));
    }

    /**
     * Returns a bag with values mapped to the number of times they are in the bag.
     *
     * Example:
     *
     *     Bag::from(['hello', 'world', 'world'])
     *         ->countValues();
     *     // => Bag of ['hello' => 1, 'world' => 2]
     *
     * @return static [value => count]
     */
    public function countValues()
    {
        return $this->createFrom(array_count_values($this->items));
    }

    /**
     * Returns a bag with the items flattened.
     *
     * Example:
     *
     *     $bag = Bag::from([[1, 2], [[3]], 4])
     *
     *     // Flatten one level
     *     $bag->flatten()
     *     // => Bag of [1, 2, [3], 4]
     *
     *     // Flatten all levels
     *     $bag->flatten(INF)
     *     // => Bag of [1, 2, 3, 4]
     *
     * @param int $depth How deep to flatten
     *
     * @return static
     */
    public function flatten($depth = 1)
    {
        return $this->createFrom(Arr::flatten($this->items, $depth));
    }

    /**
     * Returns a bag with a random number of key/value pairs.
     *
     * @param int $size Number of pairs
     *
     * @throws \InvalidArgumentException when the bag is empty or the given $size is greater than the number of items
     *
     * @return static
     */
    public function randomValues($size)
    {
        $keys = $this->randomKeys($size);

        return $keys->isEmpty() ? $keys : $this->pick($keys);
    }

    /**
     * Returns a list with a random number of keys (as values).
     *
     * @param int $size Number of keys
     *
     * @throws \InvalidArgumentException when the bag is empty or the given $size is greater than the number of items
     *
     * @return static
     */
    public function randomKeys($size)
    {
        Assert::notEmpty($this->items, 'Cannot retrieve a random key/value for empty bags.');
        Assert::range($size, 1, $this->count(), 'Expected $size to be between 1 and %3$s (the number of items in the bag). Got: %s');

        return $this->createFrom((array) array_rand($this->items, $size));
    }

    // endregion

    // region Comparison Methods

    /**
     * Returns a bag with only `$keys`.
     *
     * `$keys` should be passed in as multiple parameters if possible.
     * But if you have a list of keys they can be passed in as the first parameter.
     * Note that if you can use PHP 5.6+ syntax, you can do `pick(...$keys)` which is preferred.
     *
     * Example:
     *
     *     Bag::from([
     *         'a' => 'red',
     *         'b' => 'blue',
     *         'c' => 'green',
     *     ])
     *         ->pick('a', 'c', 'd');
     *     // => Bag of ['a' => 'red', 'c' => 'green']
     *
     * @param iterable|string|string[]|int|int[] ...$keys The keys to keep
     *
     * @return static
     */
    public function pick($keys)
    {
        // Remove accepting array as first argument once destructuring arrays is supported (PHP 5.6)
        return $this->intersectKeys(array_flip(\is_iterable($keys) ? Arr::from($keys) : func_get_args()));
    }

    /**
     * Returns a bag without `$keys`.
     *
     * `$keys` should be passed in as multiple parameters if possible.
     * But if you have a list of keys they can be passed in as the first parameter.
     * Note that if you can use PHP 5.6+ syntax, you can do `omit(...$keys)` which is preferred.
     *
     * Example:
     *
     *     Bag::from([
     *         'a' => 'red',
     *         'b' => 'blue',
     *         'c' => 'green',
     *     ])
     *         ->omit('a', 'c', 'd');
     *     // => Bag of ['b' => 'blue']
     *
     * @param iterable|string|string[]|int|int[] ...$keys The keys to remove
     *
     * @return static
     */
    public function omit($keys)
    {
        // Remove accepting array as first argument once destructuring arrays is supported (PHP 5.6)
        return $this->diffKeys(array_flip(\is_iterable($keys) ? Arr::from($keys) : func_get_args()));
    }

    /**
     * Returns a bag without the values that are also in `$collection`.
     *
     * The order is determined by this bag.
     *
     * Example:
     *
     *     Bag::from(['red', 'blue', 'green'])
     *         ->diff(['blue', 'black']);
     *     // => Bag of ['red', 'green']
     *
     * Keys are preserved, so lists could need to be re-indexed.
     *
     * @param iterable      $collection Collection to check against
     * @param callable|null $comparator Optional three-way comparison function
     *
     * @return static
     */
    public function diff($collection, callable $comparator = null)
    {
        return $this->doCompare($collection, 'array_diff', 'array_udiff', $comparator);
    }

    /**
     * Returns a bag without the values that are also in `$collection` based on the `$iteratee` function.
     *
     * Example:
     *
     *     $bag = Bag::from([
     *         ['name' => 'Alice'],
     *         ['name' => 'Bob'],
     *         ['name' => 'Carson'],
     *     ]);
     *     $itemsToRemove = [
     *         ['name' => 'Bob'],
     *         ['name' => 'Carson'],
     *         ['name' => 'David'],
     *     ];
     *
     *     // Compare each value by its 'name' property
     *     $bag->diffBy($itemsToRemove, function ($item) {
     *         return $item['name'];
     *     });
     *     // => Bag of [
     *     //     ['name' => 'Alice']
     *     // ]
     *     // Both items with name 'Bob' and 'Carson' are removed since they are also in $itemsToRemove
     *
     * Keys are preserved, so lists could need to be re-indexed.
     *
     * @param iterable $collection Collection to check against
     * @param callable $iteratee   Function is passed `($value)`
     *
     * @return static
     */
    public function diffBy($collection, callable $iteratee)
    {
        return $this->diff($collection, $this->iterateeToComparator($iteratee));
    }

    /**
     * Returns a bag without the keys that are also in `$collection`.
     *
     * The order is determined by this bag.
     *
     * Example:
     *
     *     Bag::from([
     *         'a' => 'red',
     *         'b' => 'blue',
     *         'c' => 'green',
     *     ])->diffKeys([
     *         'b' => 'value does not matter',
     *         'd' => 'something',
     *     ]);
     *     // => Bag of ['a' => 'red', 'c' => 'green']
     *
     * @param iterable      $collection Collection to check against
     * @param callable|null $comparator Optional three-way comparison function
     *
     * @return static
     */
    public function diffKeys($collection, callable $comparator = null)
    {
        return $this->doCompare($collection, 'array_diff_key', 'array_diff_ukey', $comparator);
    }

    /**
     * Returns a bag without the keys that are also in `$collection` based on the `$iteratee` function.
     *
     * Example:
     *
     *     $bag = Bag::from([
     *         'a' => 'red',
     *         'B' => 'blue',
     *         'c' => 'green',
     *     ]);
     *     $itemsToRemove = [
     *         'b' => null,
     *         'C' => null,
     *         'D' => null,
     *     ];
     *
     *     // Compare each key case-insensitively
     *     $bag->diffKeysBy($itemsToRemove, 'strtolower');
     *     // => Bag of ['a' => 'red']
     *     // Keys 'B' and 'c' are removed since all keys are compared after
     *     // being lower-cased and 'b' and 'C' are also in $itemsToRemove
     *
     * @param iterable $collection Collection to check against
     * @param callable $iteratee   Function is passed `($value)`
     *
     * @return static
     */
    public function diffKeysBy($collection, callable $iteratee)
    {
        return $this->diffKeys($collection, $this->iterateeToComparator($iteratee));
    }

    /**
     * Returns a bag with only the values that are also in `$collection`.
     *
     * Example:
     *
     *     Bag::from(['red', 'blue', 'green'])
     *         ->intersect(['blue', 'black']);
     *     // => Bag of ['blue']
     *
     * Keys are preserved, so lists could need to be re-indexed.
     *
     * @param iterable      $collection Collection to check against
     * @param callable|null $comparator Optional three-way comparison function
     *
     * @return static
     */
    public function intersect($collection, callable $comparator = null)
    {
        return $this->doCompare($collection, 'array_intersect', 'array_uintersect', $comparator);
    }

    /**
     * Returns a bag with only the values that are also in `$collection` based on the `$iteratee` function.
     *
     * Example:
     *
     *     $bag = Bag::from([
     *         ['name' => 'Alice'],
     *         ['name' => 'Bob'],
     *         ['name' => 'Carson'],
     *     ]);
     *     $itemsToKeep = [
     *         ['name' => 'Bob'],
     *         ['name' => 'Carson'],
     *         ['name' => 'David'],
     *     ];
     *
     *     // Compare each value by its 'name' property
     *     $bag->intersectBy($itemsToKeep, function ($item) {
     *         return $item['name'];
     *     });
     *     // => Bag of [
     *     //     ['name' => 'Bob']
     *     //     ['name' => 'Carson']
     *     // ]
     *     // Both items with name 'Bob' and 'Carson' are kept since they are also in $itemsToKeep
     *
     * Keys are preserved, so lists could need to be re-indexed.
     *
     * @param iterable $collection Collection to check against
     * @param callable $iteratee   Function is passed `($value)`
     *
     * @return static
     */
    public function intersectBy($collection, callable $iteratee)
    {
        return $this->intersect($collection, $this->iterateeToComparator($iteratee));
    }

    /**
     * Returns a bag with only the keys that are also in `$collection`.
     *
     * Example:
     *
     *     Bag::from([
     *         'a' => 'red',
     *         'b' => 'blue',
     *         'c' => 'green',
     *     ])->intersectKeys([
     *         'b' => 'value does not matter',
     *         'd' => 'something',
     *     ]);
     *     // => Bag of ['b' => 'blue']
     *
     * @param iterable      $collection Collection to check against
     * @param callable|null $comparator Optional three-way comparison function
     *
     * @return static
     */
    public function intersectKeys($collection, callable $comparator = null)
    {
        return $this->doCompare($collection, 'array_intersect_key', 'array_intersect_ukey', $comparator);
    }

    /**
     * Returns a bag with only the keys that are also in `$collection` based on the `$iteratee` function.
     *
     * Example:
     *
     *     $bag = Bag::from([
     *         'a' => 'red',
     *         'B' => 'blue',
     *         'c' => 'green',
     *     ]);
     *     $itemsToKeep = [
     *         'b' => null,
     *         'C' => null,
     *         'D' => null,
     *     ];
     *
     *     // Compare each key case-insensitively
     *     $bag->intersectKeysBy($itemsToKeep, 'strtolower');
     *     // => Bag of ['B' => 'blue', 'c' => 'green']
     *     // Keys 'B' and 'c' are kept since all keys are compared after
     *     // being lower-cased and 'b' and 'C' are also in $itemsToKeep
     *
     * @param iterable $collection Collection to check against
     * @param callable $iteratee   Function is passed `($key)`
     *
     * @return static
     */
    public function intersectKeysBy($collection, callable $iteratee)
    {
        return $this->intersectKeys($collection, $this->iterateeToComparator($iteratee));
    }

    /**
     * Do comparison with `$func`, or with `$funcUser` if `$comparator` is given.
     *
     * @param iterable      $collection
     * @param callable      $func
     * @param callable      $funcUser
     * @param callable|null $comparator
     *
     * @return static
     */
    private function doCompare($collection, callable $func, callable $funcUser, callable $comparator = null)
    {
        if ($comparator) {
            return $this->createFrom($funcUser($this->items, Arr::from($collection), $comparator));
        }

        return $this->createFrom($func($this->items, Arr::from($collection)));
    }

    /**
     * Returns a comparison function that calls the `$iteratee` function
     * for both values being compared before comparing them.
     *
     * @param callable $iteratee
     * @param bool     $ascending
     *
     * @return \Closure
     */
    private function iterateeToComparator(callable $iteratee, $ascending = true)
    {
        return function ($a, $b) use ($iteratee, $ascending) {
            // PHP 7.0
            // return $iteratee($a) <=> $iteratee($b);

            $a = $iteratee($a);
            $b = $iteratee($b);

            if ($a === $b) {
                return 0;
            }

            if ($ascending) {
                return $a > $b ? 1 : -1;
            }

            return $a > $b ? -1 : 1;
        };
    }

    // endregion

    // region Sorting Methods

    /**
     * Returns a bag with the values sorted.
     *
     * Sorting flags:
     * Constant                        | Description
     * ------------------------------- | ------------------------
     * `SORT_REGULAR`                  | compare values without changing types
     * `SORT_NUMERIC`                  | compare values numerically
     * `SORT_STRING`                   | compare values as strings
     * `SORT_STRING | SORT_FLAG_CASE`  | compare values as strings ignoring case
     * `SORT_LOCALE_STRING`            | compare values as strings based on the current locale
     * `SORT_NATURAL`                  | compare values as strings using "natural ordering"
     * `SORT_NATURAL | SORT_FLAG_CASE` | compare values as strings using "natural ordering" ignoring case
     *
     * @param int  $order        `SORT_ASC` or `SORT_DESC`
     * @param int  $flags        Sorting flags to modify the behavior
     * @param bool $preserveKeys Whether to preserve keys for maps or to re-index for lists
     *
     * @return static
     */
    public function sort($order = SORT_ASC, $flags = SORT_REGULAR, $preserveKeys = false)
    {
        $this->validateSortArgs($order, $flags);

        $items = $this->items;

        if (!$preserveKeys) {
            if ($order === SORT_ASC) {
                sort($items, $flags);
            } elseif ($order === SORT_DESC) {
                rsort($items, $flags);
            }
        } else {
            if ($order === SORT_ASC) {
                asort($items, $flags);
            } elseif ($order === SORT_DESC) {
                arsort($items, $flags);
            }
        }

        return $this->createFrom($items);
    }

    /**
     * Returns a bag with the values sorted based on the `$iteratee` function.
     *
     * Example:
     *
     *     $bag = Bag::from([
     *         ['name' => 'Bob'],
     *         ['name' => 'Alice'],
     *     ]);
     *
     *     // Sort values by "name" property
     *     $bag->sortBy(function ($item) {
     *         return $item['name'];
     *     });
     *     // => Bag of [
     *     //     ['name' => 'Alice']
     *     //     ['name' => 'Bob']
     *     // ]
     *
     * @param callable $iteratee     Function given `($value)`
     * @param int      $order        `SORT_ASC` or `SORT_DESC`
     * @param bool     $preserveKeys Whether to preserve keys for maps or to re-index for lists
     *
     * @return static
     */
    public function sortBy(callable $iteratee, $order = SORT_ASC, $preserveKeys = false)
    {
        return $this->sortWith($this->iterateeToComparator($iteratee, $order === SORT_ASC), $preserveKeys);
    }

    /**
     * Returns a bag with the values sorted with the `$comparator`.
     *
     * @param callable $comparator   Function given `($itemA, $itemB)`
     * @param bool     $preserveKeys Whether to preserve keys for maps or to re-index for lists
     *
     * @return static
     */
    public function sortWith(callable $comparator, $preserveKeys = false)
    {
        $items = $this->items;

        $preserveKeys ? uasort($items, $comparator) : usort($items, $comparator);

        return $this->createFrom($items);
    }

    /**
     * Returns a bag with the keys sorted.
     *
     * Sorting flags:
     * Constant                        | Description
     * ------------------------------- | ------------------------
     * `SORT_REGULAR`                  | compare values without changing types
     * `SORT_NUMERIC`                  | compare values numerically
     * `SORT_STRING`                   | compare values as strings
     * `SORT_STRING | SORT_FLAG_CASE`  | compare values as strings ignoring case
     * `SORT_LOCALE_STRING`            | compare values as strings based on the current locale
     * `SORT_NATURAL`                  | compare values as strings using "natural ordering"
     * `SORT_NATURAL | SORT_FLAG_CASE` | compare values as strings using "natural ordering" ignoring case
     *
     * @param int $order `SORT_ASC` or `SORT_DESC`
     * @param int $flags Sorting flags to modify the behavior
     *
     * @return static
     */
    public function sortKeys($order = SORT_ASC, $flags = SORT_REGULAR)
    {
        $this->validateSortArgs($order, $flags);

        $items = $this->items;

        if ($order === SORT_ASC) {
            ksort($items, $flags);
        } else {
            krsort($items, $flags);
        }

        return $this->createFrom($items);
    }

    /**
     * Returns a bag with the keys sorted based on the `$iteratee` function.
     *
     * Example:
     *
     *     $bag = Bag::from([
     *         'blue'  => 'a',
     *         'red'   => 'b',
     *         'black' => 'c',
     *     ]);
     *
     *     // Sort keys by first letter
     *     $bag->sortKeysBy(function ($key) {
     *         return $key[0];
     *     });
     *     // Bag of ['blue' => 'a', 'black' => 'c', 'red' => 'b']
     *
     * @param callable $iteratee Function given `($key)`
     * @param int      $order    `SORT_ASC` or `SORT_DESC`
     *
     * @return static
     */
    public function sortKeysBy(callable $iteratee, $order = SORT_ASC)
    {
        return $this->sortKeysWith($this->iterateeToComparator($iteratee, $order === SORT_ASC));
    }

    /**
     * Returns a bag with the keys sorted with the `$comparator`.
     *
     * @param callable $comparator Function given `($keyA, $keyB)`
     *
     * @return static
     */
    public function sortKeysWith(callable $comparator)
    {
        $items = $this->items;

        uksort($items, $comparator);

        return $this->createFrom($items);
    }

    /**
     * @param int $order
     * @param int $flags
     */
    private function validateSortArgs($order, $flags)
    {
        Assert::oneOf($order, [SORT_ASC, SORT_DESC], 'Expected $order to be SORT_ASC or SORT_DESC. Got: %s');

        Assert::oneOf(
            $flags,
            [
                SORT_REGULAR,
                SORT_NUMERIC,
                SORT_STRING,
                SORT_LOCALE_STRING,
                SORT_NATURAL,
                SORT_STRING | SORT_FLAG_CASE,
                SORT_NATURAL | SORT_FLAG_CASE,
            ],
            'Expected $flags to be one of: SORT_REGULAR, SORT_NUMERIC, SORT_STRING [ | SORT_FLAG_CASE], SORT_LOCALE_STRING, or SORT_NATURAL [ | SORT_FLAG_CASE]. Got: %s'
        );
    }

    /**
     * Returns a bag with the items reversed.
     *
     * @param bool $preserveKeys If true numeric keys are preserved. Non-numeric keys are always preserved.
     *
     * @return static
     */
    public function reverse($preserveKeys = false)
    {
        return $this->createFrom(array_reverse($this->items, $preserveKeys));
    }

    /**
     * Returns a bag with the items shuffled.
     *
     * @return static
     */
    public function shuffle()
    {
        $items = $this->items;

        shuffle($items);

        return $this->createFrom($items);
    }

    //endregion

    // region Mutating Methods (Deprecated)

    /**
     * Adds an item to the end of this bag.
     *
     * @param mixed $item The item to append
     *
     * @deprecated since 1.1 and will be removed in 2.0. Use {@see MutableBag} instead.
     */
    public function add($item)
    {
        Deprecated::method(1.1, MutableBag::class);

        $this->items[] = $item;
    }

    /**
     * Adds an item to the beginning of this bag.
     *
     * @param mixed $item The item to prepend
     *
     * @deprecated since 1.1 and will be removed in 2.0. Use {@see MutableBag} instead.
     */
    public function prepend($item)
    {
        Deprecated::method(1.1, MutableBag::class);

        array_unshift($this->items, $item);
    }

    /**
     * Sets a item by key.
     *
     * @param string $key   The key
     * @param mixed  $value The value
     *
     * @deprecated since 1.1 and will be removed in 2.0. Use {@see MutableBag} instead.
     */
    public function set($key, $value)
    {
        Deprecated::method(1.1, MutableBag::class);

        $this->items[$key] = $value;
    }

    /**
     * Sets a value using path syntax to set nested data.
     * Inner arrays will be created as needed to set the value.
     *
     * Example:
     *
     *     // Set an item at a nested structure
     *     $bag->setPath('foo/bar', 'color');
     *
     *     // Append to a list in a nested structure
     *     $bag->get($data, 'foo/baz');
     *     // => null
     *     $bag->setPath('foo/baz/[]', 'a');
     *     $bag->setPath('foo/baz/[]', 'b');
     *     $bag->getPath('foo/baz');
     *     // => ['a', 'b']
     *
     * This function does not support keys that contain `/` or `[]` characters
     * because these are special tokens used when traversing the data structure.
     * A value may be appended to an existing array by using `[]` as the final
     * key of a path.
     *
     * <br>
     * Note: To set values in arrays that are in `ArrayAccess` objects their
     * `offsetGet()` method needs to be able to return arrays by reference.
     * See {@see MutableBag} for an example of this.
     *
     * @param string $path  The path to traverse and set the value at
     * @param mixed  $value The value to set
     *
     * @throws \RuntimeException when trying to set a path that travels through a scalar value
     * @throws \RuntimeException when trying to set a value in an array that is in an `ArrayAccess` object
     *                           which cannot retrieve arrays by reference
     *
     * @deprecated since 1.1 and will be removed in 2.0. Use {@see MutableBag} instead.
     */
    public function setPath($path, $value)
    {
        Deprecated::method(1.1, MutableBag::class);

        Arr::set($this->items, $path, $value);
    }

    /**
     * Remove all items from bag.
     *
     * @deprecated since 1.1 and will be removed in 2.0. Use {@see MutableBag} instead.
     */
    public function clear()
    {
        Deprecated::method(1.1, MutableBag::class);

        $this->items = [];
    }

    /**
     * Removes and returns the item at the specified `$key` from the bag.
     *
     * @param string|int $key     The key of the item to remove
     * @param mixed|null $default The default value to return if the key is not found
     *
     * @return mixed The removed item or `$default`, if the bag did not contain the item
     *
     * @deprecated since 1.1 and will be removed in 2.0. Use {@see MutableBag} instead.
     */
    public function remove($key, $default = null)
    {
        Deprecated::method(1.1, MutableBag::class);

        if (!$this->has($key)) {
            return $default;
        }

        $removed = $this->items[$key];
        unset($this->items[$key]);

        return $removed;
    }

    /**
     * Removes the given item from the bag if it is found.
     *
     * @param mixed $item
     *
     * @deprecated since 1.1 and will be removed in 2.0. Use {@see MutableBag} instead.
     */
    public function removeItem($item)
    {
        Deprecated::method(1.1, MutableBag::class);

        $key = array_search($item, $this->items, true);

        if ($key !== false) {
            unset($this->items[$key]);
        }
    }

    /**
     * Removes and returns the first item in the list.
     *
     * @return mixed|null
     *
     * @deprecated since 1.1 and will be removed in 2.0. Use {@see MutableBag} instead.
     */
    public function removeFirst()
    {
        Deprecated::method(1.1, MutableBag::class);

        return array_shift($this->items);
    }

    /**
     * Removes and returns the last item in the list.
     *
     * @return mixed|null
     *
     * @deprecated since 1.1 and will be removed in 2.0. Use {@see MutableBag} instead.
     */
    public function removeLast()
    {
        Deprecated::method(1.1, MutableBag::class);

        return array_pop($this->items);
    }

    // endregion

    // region Internal Methods

    /**
     * Don't call directly. Used for IteratorAggregate.
     *
     * @internal
     *
     * @inheritdoc
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * Don't call directly. Used for JsonSerializable.
     *
     * @internal
     *
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return $this->items;
    }

    /**
     * Don't call directly. Used for ArrayAccess.
     *
     * @internal
     *
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Don't call directly. Used for ArrayAccess.
     *
     * @internal
     *
     * @inheritdoc
     */
    public function &offsetGet($offset)
    {
        // Returning values by reference is deprecated, but we have no way of knowing here.
        //return $this->get($offset);

        $result = null;
        if (isset($this->items[$offset])) {
            $result = &$this->items[$offset];
        }

        return $result;
    }

    /**
     * Don't call directly. Used for ArrayAccess.
     *
     * @internal
     *
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        //throw new BadMethodCallException(sprintf('Cannot modify items on an %s', __CLASS__));

        Deprecated::method(1.1, MutableBag::class);

        if ($offset === null) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * Don't call directly. Used for ArrayAccess.
     *
     * @internal
     *
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        //throw new BadMethodCallException(sprintf('Cannot remove items from an %s', __CLASS__));

        Deprecated::method(1.1, MutableBag::class);

        unset($this->items[$offset]);
    }

    /**
     * Don't call directly. Used for debugging.
     *
     * @internal
     */
    public function __debugInfo()
    {
        return $this->items;
    }

    /**
     * Don't call directly. Used for debugging.
     *
     * xdebug needs this to be able to display nested items properly.
     * For example: We say this bag has a "foo" key, so xdebug does `$this->foo`.
     *
     * @internal
     *
     * @inheritdoc
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    // endregion
}

// Alias class for BC. Alias is needed, instead of subclassing, so `Bag instanceof ImmutableBag` works.
class_alias(Bag::class, ImmutableBag::class);
