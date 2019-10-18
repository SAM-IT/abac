<?php
declare(strict_types=1);

namespace test;

use SamIT\abac\engines\SimpleEngine;
use SamIT\abac\interfaces\RuleEngine;

class SimpleEngineTest extends RuleEngineTest
{

    public function testConstructor()
    {
        $this->expectException(\InvalidArgumentException::class);
        new SimpleEngine([
            new \stdClass()
        ]);
    }
    protected function getEngine(iterable $rules): RuleEngine
    {
        return new SimpleEngine($rules);
    }


}