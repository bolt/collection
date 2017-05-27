# Bolt Collection

This library provides objects and functionality to help with groups of items and data sets.

## `Bag` and `ImmutableBag`

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
$colors = Bag::from(['red', 'blue', 'yellow'])
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

These examples only cover half of the functionality.

All methods asking for a collection will accept other `Bags`, `arrays`,
`stdClass`, and `Traversable` objects. This makes it very easy work with any
collection-like object. 

`Nulls` and `mixed` values are also supported which are converted to empty
arrays and an array with one item respectively.


### What's `ImmutableBag` for?

All of the methods in `ImmutableBag` are queries of the current state or return
a _new_ modified Bag. Thus an `ImmutableBag` cannot be mutated. `Bag` extends
`ImmutableBag` and provides methods to add, modify, and remove items. 

In PHP, arrays are always passed/returned by value, but objects are 
passed/returned by reference. 

Let's look at an example:

```php
class Foo
{
    /** @var array */
    private $items;

    public function getItems()
    {
        return $this->items;
    }
}

$foo = new Foo();
$items = $foo->getItems();
$items[] = 'hello';
```

Since `Foo::$items` is an array, the `$items` returned from `getItems()` is a
different array. Thus appending "hello" to the array does not modify 
`Foo::$items`.

Now pretend `Foo::$items` is a `Bag`. Since it is an object, the `$items`
returned from `getItems()` is the same object as `Foo::$items`. This means you
can modify `Foo`'s internal state externally. `Foo` may not want this, so it
can return an `ImmutableBag` instead. 

It could be argued that a clone of the Bag could be returned from the method
instead. This is true, but it would lead to more processing for each query of
the bag, and could lead to WTF's if the user is trying to modify the clone and
it's not applying to the object.

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
