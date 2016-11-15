<?php
namespace CSVelteTest\Collection;

use CSVelte\Collection\Collection;
use CSVelte\Collection\NumericCollection;
use CSVelteTest\UnitTestCase;

class NumericCollectionTest extends UnitTestCase
{
    public function testIncrementDecrementAddsSubtractsOneFromGivenKey()
    {
        $coll = Collection::factory([10,15,20,25,50,100]);
        $zero = 0;
        $this->assertInstanceOf(NumericCollection::class, $coll);
        $coll->increment($zero);
        $this->assertEquals(11, $coll->get($zero));
        $coll->increment($zero);
        $coll->increment($zero);
        $coll->increment($zero);
        $coll->increment($zero);
        $this->assertEquals(15, $coll->get($zero));
        $coll->decrement($zero);
        $this->assertEquals(14, $coll->get($zero));
        $coll->decrement($zero);
        $coll->decrement($zero);
        $this->assertEquals(12, $coll->get($zero));
    }

    public function testIncrementDecrementWithIntervalAddsSubtractsIntervalFromGivenKey()
    {
        $coll = Collection::factory([10,15,20,25,50,100]);
        $zero = 0;
        $this->assertInstanceOf(NumericCollection::class, $coll);
        $coll->increment($zero, 5);
        $this->assertEquals(15, $coll->get($zero));
        $coll->increment($zero, 100);
        $this->assertEquals(115, $coll->get($zero));
        $coll->decrement($zero, 2);
        $this->assertEquals(113, $coll->get($zero));
        $coll->decrement($zero, 1000);
        $coll->decrement($zero);
        $this->assertEquals(-888, $coll->get($zero));
    }
}