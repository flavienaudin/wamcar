<?php

namespace Test\Unit;

use atoum;

class Dummy extends atoum
{
    /** this is a dummy test to check test setup before implementing real ones */
    public function dummyTest()
    {
        $this->assert->boolean(true)->isTrue();
    }
}
