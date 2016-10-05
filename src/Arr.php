<?php

namespace Bolt\Collection;

/**
 * Array helper functions.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class Arr
{
    /**
     * Returns whether the given item is an associative array.
     *
     * Note: Empty arrays are not.
     *
     * @param array $array
     *
     * @return bool
     */
    public static function isAssociative($array)
    {
        if (!is_array($array) || $array === []) {
            return false;
        }

        return array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * Returns whether the given item is an indexed array - zero indexed and sequential.
     *
     * Note: Empty arrays are.
     *
     * @param array $array
     *
     * @return bool
     */
    public static function isIndexed($array)
    {
        if (!is_array($array)) {
            return false;
        }

        return !static::isAssociative($array);
    }

    /**
     * Replaces values from second array into first array recursively.
     *
     * This differs from {@see array_replace_recursive} in a couple ways:
     *  - Lists (indexed arrays) from second array completely replace list in first array.
     *  - Null values from second array do not replace lists or associative arrays in first
     *    (they do still replace scalar values).
     *
     * @param array $array1
     * @param array $array2
     *
     * @return array The combined array
     */
    public static function replaceRecursive(array $array1, array $array2)
    {
        $merged = $array1;

        foreach ($array2 as $key => $value) {
            if (is_array($value) && static::isAssociative($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = static::replaceRecursive($merged[$key], $value);
            } elseif ($value === null && isset($merged[$key]) && is_array($merged[$key])) {
                continue;
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Private Constructor.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }
}
