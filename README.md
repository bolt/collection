# Bolt Collection

[![travis](https://api.travis-ci.org/bolt/collection.svg)](https://travis-ci.org/bolt/collection)
[![codecov](https://codecov.io/gh/bolt/collection/branch/master/graph/badge.svg)](https://codecov.io/gh/bolt/collection)

This library provides objects and functionality to help with groups of items and data sets.

Check out the [API documentation][api-docs].

## `Bag` and `MutableBag`

These are object-oriented implementations of arrays. 

The goal of these classes:
  - Provide functionality on par with built-in array methods
  - Provide useful functionality lacking from built-in array methods
  - Provide a fluent-like experience
  - Make implementing code more readable

### Examples
```php
$arr = [
    'debug' => true,
    'color' => 'blue',
    'db' => [
        'driver' => 'sqlite',
    ],
];

$bag = Bag::from($arr)
    ->defaults([
        'debug' => false,
        'color' => 'green',
    ])
    ->replace([
        'color' => 'red',
    ])
;
$bag->get('debug'); // true
$bag->getPath('db/driver'); // "sqlite"
$bag->keys()->join(', '); // "debug, color, db"
$bag->isAssociative(); // true
```
```php
$colors = MutableBag::of('red', 'blue', 'yellow')
    ->merge(['green', 'orange'])
;
$colors->isIndexed(); // true
$colors->indexOf('yellow'); // 2
$colors[2]; // "yellow"

$colors->prepend('purple');
$colors[] = 'pink';

$colors->first(); // "purple"
$colors->last(); // "pink"

$colors->shuffle()->first(); // one of the colors

$colors->chunk(2); // Bags represented as arrays:
// [ ['purple', 'red'], ['blue', 'yellow'], ['green, 'orange'], ['pink'] ]

$colors->removeFirst(); // "purple"
$colors->removeFirst(); // "red"
```

These examples only cover half of the functionality. See the [API documentation][api-docs] for more.

All methods accepting a collection will accept other `Bags`, `arrays`,
`stdClass`, and `Traversable` objects. This makes it very easy work with any
collection-like object. 


### Hasn't this been done already?

Obviously others think PHP arrays suck as well and have attempted to resolve 
this.
 
Symfony's `ParameterBag` is a good basic _map_ (associative arrays) container
but is lacking when it comes to mutating the items around and working with
lists.

Doctrine's `ArrayCollection` is also another, more robust, option. It works
well for maps and lists, but still has limited functionality due to needing to
interface with a database collection. It also has some annoyances, like 
`getKeys()` returns an `array` instead of another `ArrayCollection` instance.

[api-docs]: https://docs.bolt.cm/api/bolt/collection/master/classes.html
