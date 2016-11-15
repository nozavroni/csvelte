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


    public function testSumMethodSumsCollection()
    {
        $coll = Collection::factory([10,20,30,100,60,80]);
        $this->assertEquals(300, $coll->sum());
    }

    public function testAverageMethodAveragesCollection()
    {
        $coll = Collection::factory([10,20,30,100,60,80]);
        $this->assertEquals(50, $coll->average());
    }

    public function testModeMethodReturnsCollectionMode()
    {
        $coll = Collection::factory([10,20,30,100,60,80,10,20,100,10,50,40,10,20,50,60,80]);
        $this->assertEquals(10, $coll->mode());
    }

    public function testMedianMethodReturnsCollectionMedian()
    {
        $coll = Collection::factory([1,10,20,30,100,60,80,10,20,100,10,50,40,10,20,50,60,80]);
        $this->assertEquals(35, $coll->median());

        $coll = Collection::factory([1,20,300,4000]);
        $this->assertEquals(160, $coll->median());

        // $coll = Collection::factory(['one','two','three','four','five']);
        // $this->assertEquals('four', $coll->median());

        // @todo Maybe for strings median should work with string length?
        // $coll = Collection::factory(['hello','world','this','will','do','weird','stuff','yes','it','will']);
        // $this->assertEquals(0, $coll->median());

        $coll = Collection::factory([1]);
        $this->assertEquals(1, $coll->median());

        $coll = Collection::factory([1,2]);
        $this->assertEquals(1.5, $coll->median());
    }

    public function testCountsReturnsCollectionOfCounts()
    {
        $data = [1,1,1,2,0,2,2,3,3,3,3,3,3,3,4,5,6,6,7,8,9,0];
        $coll = Collection::factory($data);
        $this->assertInstanceOf(NumericCollection::class, $coll);
        $counts = $coll->counts();
        $this->assertInstanceOf(NumericCollection::class, $counts);
        $this->assertEquals([
            1 => 3,
            2 => 3,
            3 => 7,
            4 => 1,
            5 => 1,
            6 => 2,
            7 => 1,
            8 => 1,
            9 => 1,
            0 => 2
        ], $counts->toArray());
    }

}