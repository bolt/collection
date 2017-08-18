CHANGELOG for Bolt Collection
=============================

1.1.1
-----

Released 2017-08-18 ([commits since v1.1.0](https://github.com/bolt/collection/compare/v1.1.0...v1.1.1))

- Change: `Arr::set` classes `offsetGet` is only called once per key now in case there is non-light logic in that method
- Change: `Arr::set` now it only gets a known key from an AA object and doesn't need to set a test value
- Fixed: `Arr::set` not throwing exception when object cannot return an array by reference for a pre-existing key
- Fixed: `Arr::set` false positives for incorrect logic in AA::offsetGet() methods
- Fixed: `Arr::set` deprecation warnings not to trigger for `MutableBag`
- Fixed: `Arr::set` exception message to reference `MutableBag` instead of `Bag`

1.1.0
-----

Released 2017-08-10 ([commits since v1.0.0](https://github.com/bolt/collection/compare/v1.0.0...v1.1.0))

 - PR [#23](https://github.com/bolt/collection/pull/23)
   - Change: Make `ImmutableBag` a class alias
 - PR [#22](https://github.com/bolt/collection/pull/22)
   - Change: Replace `Traversable|array` in PHPDoc with `iterable`
 - PR [#21](https://github.com/bolt/collection/pull/21)
   - Added: `Bag::of()` method
 - PR [#20](https://github.com/bolt/collection/pull/20)
   - Change: (Optimisation) Added Collection classes by default to canReturnArrayByReference list
 - PR [#19](https://github.com/bolt/collection/pull/19)
   - Added: `Bag::randomValue()` method
   - Added: `Bag::randomValues()` method
   - Added: `Bag::randomKey()` method
   - Added: `Bag::randomKeys()` method
 - PR [#18](https://github.com/bolt/collection/pull/18)
   - Added: `Bag::pick()` method
   - Added: `Bag::omit()` method
 - PR [#17](https://github.com/bolt/collection/pull/17)
   - Added: `Arr::remove()` method
   - Added: `MutableBag::removePath()` method
 - PR [#16](https://github.com/bolt/collection/pull/16)
   - Added: `Bag::sort()` method
   - Added: `Bag::sortBy()` method
   - Added: `Bag::sortWith()` method
   - Added: `Bag::sortKeys()` method
   - Added: `Bag::sortKeysBy()` method
   - Added: `Bag::sortKeysWith()` method
 - PR [#15](https://github.com/bolt/collection/pull/15)
   - Added: `Bag::flatten()` method
 - PR [#14](https://github.com/bolt/collection/pull/14)
   - Change: Move `countValues()` to `ImmutableBag` to be consistent for 1.x
             All the `ImmutableBag` methods will be moved to Bag in 2.0
 - PR [#13](https://github.com/bolt/collection/pull/13)
   - Added: `Bag::pad()` method
 - PR [#12](https://github.com/bolt/collection/pull/12)
   - Added: `Bag::diff()` method
   - Added: `Bag::diffBy()` method
   - Added: `Bag::diffKeys()` method
   - Added: `Bag::diffKeysBy()` method
   - Added: `Bag::intersect()` method
   - Added: `Bag::intersectBy()` method
   - Added: `Bag::intersectKeys()` method
   - Added: `Bag::intersectKeysBy()` method
 - PR [#11](https://github.com/bolt/collection/pull/11)
   - Break: `Bag::first()` & `Bag::last()` failures now return `null`
 - PR [#10](https://github.com/bolt/collection/pull/10)
   - Added: `Bag::call()` method
 - PR [#9](https://github.com/bolt/collection/pull/9)
   - Added: `Arr::mapRecursive()` method
 - PR [#8](https://github.com/bolt/collection/pull/8)
   - Added: `Bag::reject()` method
 - PR [#7](https://github.com/bolt/collection/pull/7)
   - Break: `Bag::indexOf()` changed the failure return value from `false` to `null`
   - Added: `Bag::indexOf()` optional second parameter `$fromIndex`
   - Added: `Bag::lastIndexOf()` method
   - Added: `Bag::find()` method
   - Added: `Bag::findLast()` method
   - Added: `Bag::findKey()` method
   - Added: `Bag::findLastKey()` method
 - PR [#6](https://github.com/bolt/collection/pull/6)
   - Added: `Bag::countValues()`
 - PR [#5](https://github.com/bolt/collection/pull/5)
   - Break: Previously non-iterables would be converted to arrays with one item, now will throw an exception
   - Change: Update `Bag` to use `Arr::from`
   - Added: `Arr::from()` & `Arr:fromRecursive()` to take iterables and convert to arrays
 - PR [#4](https://github.com/bolt/collection/pull/4)
   - Change: `Bag` -> `MutableBag`
   - Change: `ImmutableBag` -> `Bag`
   - Deprecated: `Bag` mutating methods, use `MutableBag`
   - Deprecated: `ImmutableBag` class, use `Bag`
 - PR [#3](https://github.com/bolt/collection/pull/3)
   - Added: `bolt/common`
   - Updated: Usages of `Assert` to use `bolt/common`
   - Deprecated: `Arr:: assertAccessible`
 - PR [#2](https://github.com/bolt/collection/pull/2)
   - Added: Code styling & test integration

1.0.0
-----

Released 2017-05-27 ([commits](https://github.com/bolt/collection/compare/cf95a9a7bb4b1fc4699efb8bdcba79b77349393f...v1.0.0))

Change summary:

 - Added: `Arr` class
 - Added: `Bag` class
 - Added: `ImmutableBag` class
