<?php

declare(strict_types=1);

namespace test;

use SamIT\abac\engines\SimpleEngine;
use SamIT\abac\interfaces\RuleEngine;
use SamIT\abac\interfaces\SimpleRule;

/**
 * @covers \SamIT\abac\engines\SimpleEngine
 */
class SimpleEngineTest extends RuleEngineTest
{
    protected function getEngine(SimpleRule ...$rules): RuleEngine
    {
        return new SimpleEngine(...$rules);
    }
}
