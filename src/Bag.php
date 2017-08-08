<?php

namespace Bolt\Collection;

use Bolt\Common\Deprecated;

/**
 * This is an OO implementation of almost all of PHP's array functionality.
 *
 * All methods that allow mutation are deprecated, use {@see MutableBag} for those cases instead.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class Bag extends ImmutableBag
{
    // region Creation / Unwrapping Methods

    /**
     * Constructor.
     *
     * @param array $items
     */
    public function __construct(array $items = [])
    {
        // Don't call parent to avoid deprecation warning.
        $this->items = $items;
    }

    // endregion

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
     * Sets a value at the path given.
     * Keys will be created as needed to set the value.
     *
     * This function does not support keys that contain "/" or "[]" characters
     * because these are special tokens used when traversing the data structure.
     * A value may be appended to an existing array by using "[]" as the final
     * key of a path.
     *
     *     // Set an item at a nested structure.
     *     setPath('foo/bar', 'color');
     *
     *     // Append to a list in a nested structure.
     *     setPath('foo/baz/[]', 'a');
     *     setPath('foo/baz/[]', 'b');
     *     getPath('foo/baz'); // returns ['a', 'b']
     *
     * Note: To set values not directly under ArrayAccess objects their
     * offsetGet() method needs to be defined as return by reference.
     *
     *     public function &offsetGet($offset) {}
     *
     * @param string $path  The path to traverse and set the value at
     * @param mixed  $value The value to set
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
     * Removes and returns the item at the specified key from the bag.
     *
     * @param string|int $key     The kex of the item to remove
     * @param mixed|null $default The default value to return if the key is not found
     *
     * @return mixed The removed item or default, if the bag did not contain the item
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
     * Don't call directly. Used for ArrayAccess.
     *
     * @internal
     *
     * @inheritdoc
     */
    public function &offsetGet($offset)
    {
        // Returning values by reference is deprecated, but we have no way of knowing here.

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
        Deprecated::method(1.1, MutableBag::class);

        unset($this->items[$offset]);
    }

    // endregion
}
