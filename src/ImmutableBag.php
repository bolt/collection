<?php

namespace Bolt\Collection;

// Load Bag.php which defines class alias
class_exists(Bag::class);

// @codingStandardsIgnoreLine
return;

// Keep class definition for IDE completion and docs (even though it isn't loaded)
/**
 * @deprecated since 1.1 and will be removed in 2.0. Use {@see Bag} instead.
 */
class ImmutableBag extends Bag
{
}
