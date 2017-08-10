<?php

namespace Bolt\Collection;

/**
 * An OO implementation of PHP's array functionality and more.
 *
 * Generally only methods dealing with a single item mutate the current bag,
 * all others return a new bag.
 *
 * <br>
 * Be careful when exposing a `MutableBag` publicly, like with a getter method.
 *
 * It is an object so it is returned by reference,
 * as opposed to arrays that are returned by value.
 * This means someone could take the bag and modify it, and since it is the same
 * instance the modifications are applied to the bag in your class as well.
 * There could be some use-cases for this as long as you are aware of it.
 *
 * This can be mitigated by type-hinting or documenting that the return value is
 * a `Bag`, since `MutableBag` _extends_ `Bag`, so only read-only access is allowed.
 *
 * This still returns the `MutableBag` though so the documented API could
 * be broken and the bag modified anyways.
 * If you want to _ensure_ that the bag cannot be not modified, you can return a
 * copy of the bag with the getter method. You can copy the bag by calling
 * the {@see Bag::immutable} or {@see mutable} methods or by _cloning_ it.
 * This does have a performance penalty though, as every call to the getter creates
 * a new bag. Also if the documented return value is still `MutableBag` it could
 * be confusing to users trying to modify the bag and not seeing results take affect.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class MutableBag extends Bag
{
    // region Mutating Methods

    /**
     * Adds an item to the end of this bag.
     *
     * @param mixed $item The item to append
     */
    public function add($item)
    {
        $this->items[] = $item;
    }

    /**
     * Adds an item to the beginning of this bag.
     *
     * @param mixed $item The item to prepend
     */
    public function prepend($item)
    {
        array_unshift($this->items, $item);
    }

    /**
     * Sets a value by key.
     *
     * @param string $key   The key
     * @param mixed  $value The value
     */
    public function set($key, $value)
    {
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
     */
    public function setPath($path, $value)
    {
        Arr::set($this->items, $path, $value);
    }

    /**
     * Remove all items from bag.
     */
    public function clear()
    {
        $this->items = [];
    }

    /**
     * Removes and returns the item at the specified `$key` from the bag.
     *
     * @param string|int $key     The key of the item to remove
     * @param mixed|null $default The default value to return if the key is not found
     *
     * @return mixed The removed item or `$default`, if the bag did not contain the item
     */
    public function remove($key, $default = null)
    {
        if (!$this->has($key)) {
            return $default;
        }

        $removed = $this->items[$key];
        unset($this->items[$key]);

        return $removed;
    }

    /**
     * Removes and returns a value using path syntax to retrieve nested data.
     *
     * Example:
     *
     *     $bag->removePath('foo/bar');
     *     // => 'baz'
     *     $bag->removePath('foo/bar');
     *     // => null
     *
     * This function does not support keys that contain `/`.
     *
     * <br>
     * Note: To remove values in arrays that are in `ArrayAccess` objects their
     * `offsetGet()` method needs to be able to return arrays by reference.
     * See {@see MutableBag} for an example of this.
     *
     * @param string     $path    Path to traverse and remove the value at
     * @param mixed|null $default Default value to return if key does not exist
     *
     * @throws \RuntimeException when trying to set a path that travels through a scalar value
     * @throws \RuntimeException when trying to set a value in an array that is in an `ArrayAccess` object
     *                           which cannot retrieve arrays by reference
     *
     * @return mixed
     */
    public function removePath($path, $default = null)
    {
        return Arr::remove($this->items, $path, $default);
    }

    /**
     * Removes the given item from the bag if it is found.
     *
     * @param mixed $item
     */
    public function removeItem($item)
    {
        $key = array_search($item, $this->items, true);

        if ($key !== false) {
            unset($this->items[$key]);
        }
    }

    /**
     * Removes and returns the first item in the list.
     *
     * @return mixed|null
     */
    public function removeFirst()
    {
        return array_shift($this->items);
    }

    /**
     * Removes and returns the last item in the list.
     *
     * @return mixed|null
     */
    public function removeLast()
    {
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
        if ($offset === null) {
            $this->add($value);
        } else {
            $this->set($offset, $value);
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
        $this->remove($offset);
    }

    // endregion
}
