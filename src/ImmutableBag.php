<?php

namespace Bolt\Collection;

use ArrayAccess;
use BadMethodCallException;
use Bolt\Common\Deprecated;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use JsonSerializable;
use RuntimeException;
use stdClass;
use Traversable;

/**
 * This is an OO implementation of almost all of PHP's array functionality.
 *
 * But there are no methods that allow the object to be mutated. All methods return a new bag.
 *
 * @author Carson Full <carsonfull@gmail.com>
 *
 * @deprecated since 1.1 and will be removed in 2.0. Use {@see Bag} instead.
 */
class ImmutableBag implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
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
        Deprecated::method(1.1, Bag::class);

        $this->items = $items;
    }

    /**
     * Create a bag from a variety of collections.
     *
     * @param Traversable|array|stdClass|null $collection
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
     * @param Traversable|array|stdClass|null $collection
     *
     * @return static
     */
    public static function fromRecursive($collection)
    {
        $arr = Arr::from($collection);

        foreach ($arr as $key => $value) {
            if ($value instanceof stdClass || is_iterable($value)) {
                $value = static::fromRecursive($value);
            }
            $arr[$key] = $value;
        }

        return new static($arr);
    }

    /**
     * Creates a bag by using one collection for keys and another for its values.
     *
     * @param Traversable|array $keys
     * @param Traversable|array $values
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
     * Returns whether an item exists for the key defined by the given path.
     *
     *     hasPath('foo/bar/baz') // true
     *
     * This method does not allow for keys that contain "/".
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
     * Returns true if the item is in the bag.
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
     * Returns an item using a path syntax to retrieve nested data.
     *
     *     getPath('foo/bar/baz') // baz item
     *
     * This method does not allow for keys that contain "/".
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
     * Gets the index/key of a given item. The comparison of two items is strict,
     * that means not only the value but also the type must match.
     * For objects this means reference equality.
     *
     * @param mixed $item The item to search for
     *
     * @return int|string|false The index or key of the item or false if the item was not found
     */
    public function indexOf($item)
    {
        return array_search($item, $this->items, true);
    }

    /**
     * Returns the first item in the list.
     *
     * @return mixed
     */
    public function first()
    {
        return reset($this->items);
    }

    /**
     * Returns the last item in the list.
     *
     * @return mixed
     */
    public function last()
    {
        return end($this->items);
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

    // endregion

    // region Methods returning a new bag

    /**
     * Calls the $callable with the items (array) as the first parameter which should return an iterable which is
     * then converted to a bag. Any extra parameters passed in to this method are passed to the $callable after
     * the items parameter.
     *
     * This allows for chain-ability with custom functionality.
     *
     * <br>
     * Example:
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
     * Example with args:
     *     Bag::from(['red', 'blue'])->call('array_pad', 4, '');
     *     // => Bag ['red', 'blue', '', '']
     *
     * @param callable $callable
     * @param array    ...$args
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
        return new Bag($this->items);
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
     * Applies the given function to each item in the bag and returns
     * a new bag with the items returned by the function.
     *
     * Note: This differs from array_map in that the callback is passed $key first, then $value.
     *
     * @param callable $callback Function is passed (key, value)
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
     * @param callable $callback Function is passed (key, value)
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
     * Returns a bag with the items that satisfy the predicate $callback.
     *
     * Keys are preserved, so lists could need to be re-indexed.
     *
     * Note: This differs from array_filter in that the callback is passed $key first, then $value.
     *
     * @param callable $callback The predicate used for filtering. Function is passed (key, value).
     *
     * @return static
     */
    public function filter(callable $callback)
    {
        $items = [];

        foreach ($this->items as $key => $value) {
            if ($callback($key, $value)) {
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
     * Replaces items in this bag from the given collection by comparing keys and returns the result.
     *
     * @param Traversable|array $collection The collection from which items will be extracted
     *
     * @return static
     */
    public function replace($collection)
    {
        return $this->createFrom(array_replace($this->items, Arr::from($collection)));
    }

    /**
     * Returns a bag with the items replaced recursively from the given collection.
     *
     * This differs from {@see array_replace_recursive} in a couple ways:
     *  - Lists (zero indexed and sequential items) from given collection completely replace lists in this Bag.
     *  - Null values from given collection do not replace lists or associative arrays in this Bag
     *    (they do still replace scalar values).
     *
     * @param Traversable|array $collection The collection from which items will be extracted
     *
     * @return static
     */
    public function replaceRecursive($collection)
    {
        return $this->createFrom(Arr::replaceRecursive($this->items, Arr::from($collection)));
    }

    /**
     * Returns a bag with the items from the given collection added to the items in this bag
     * if they do not already exist by comparing keys. The opposite of replace().
     *
     * @param Traversable|array $collection The collection from which items will be extracted
     *
     * @return static
     */
    public function defaults($collection)
    {
        return $this->createFrom(array_replace(Arr::from($collection), $this->items));
    }

    /**
     * Returns a bag with the items from the given collection recursively added to the items in this bag
     * if they do not already exist by comparing keys. The opposite of replaceRecursive().
     *
     * @param Traversable|array $collection The collection from which items will be extracted
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
     * For associative arrays, use replace instead.
     *
     * @param Traversable|array $list The list of items to merge
     *
     * @return static
     */
    public function merge($list)
    {
        return $this->createFrom(array_merge($this->items, Arr::from($list)));
    }

    /**
     * Returns a bag with a slice of $length items starting at position $offset extracted from this bag.
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
     * Partitions the items into two bags according to the callback function.
     * Keys are preserved in the resulting bags.
     *
     *     [$trueItems, $falseItems] = $bag->partition(function ($key, $item) {
     *         return true; // whatever logic
     *     });
     *
     * @param callable $callback The function is passed (key, value) and should return a boolean
     *
     * @return static[] [true bag, false bag]
     */
    public function partition(callable $callback)
    {
        $coll1 = $coll2 = [];

        foreach ($this->items as $key => $item) {
            if ($callback($key, $item)) {
                $coll1[$key] = $item;
            } else {
                $coll2[$key] = $item;
            }
        }

        return [$this->createFrom($coll1), $this->createFrom($coll2)];
    }

    /**
     * Returns a bag with the values from a single column, identified by the $columnKey.
     *
     * Optionally, an $indexKey may be provided to index the values in the
     * returned Bag by the values from the $indexKey column.
     *
     * @param string      $columnKey Column of values to return
     * @param string|null $indexKey  Column to use as the index/keys for the returned items
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
     * @throws RuntimeException when flip fails
     *
     * @return static
     */
    public function flip()
    {
        $arr = array_flip($this->items);
        if ($arr === null) {
            throw new RuntimeException('Failed to flip the items.');
        }

        return $this->createFrom($arr);
    }

    /**
     * Iteratively reduce the items to a single value using a callback function.
     *
     * @param callable $callback Function is passed $carry (previous or initial value)
     *                           and $item (value of the current iteration)
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
     *     $bag = new Bag([1, 2, 3, 4, 5]);
     *     $bag->chunk(2); // returns [[1, 2], [3, 4], [5]] but as bags not arrays.
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

    // endregion

    // region Sorting Methods

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

    // region Internal Methods

    /**
     * {@inheritdoc}
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
    public function offsetGet($offset)
    {
        return $this->get($offset);
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
        throw new BadMethodCallException(sprintf('Cannot modify items on an %s', __CLASS__));
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
        throw new BadMethodCallException(sprintf('Cannot remove items from an %s', __CLASS__));
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
