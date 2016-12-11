<?php
namespace CSVelteTest\Collection;

use BadMethodCallException;
use CSVelte\Collection\AbstractCollection;
use CSVelte\Collection\Collection;
use CSVelte\Collection\MultiCollection;

use CSVelte\Collection\TabularCollection;
use function CSVelte\is_traversable;

class TabularCollectionTest extends AbstractCollectionTest
{
    protected $moretestdata = [
        [
            'numbers' => 10,
            'words' => 'some words'
        ],
        [
            'numbers' => 17,
            'words' => 'some words'
        ],
        [
            'numbers' => 18,
            'words' => 'some words'
        ],
        [
            'numbers' => 11,
            'words' => 'no words'
        ],
        [
            'numbers' => 20,
            'words' => 'all words'
        ],
        [
            'numbers' => 15,
            'words' => 'word up'
        ],
        [
            'numbers' => 15,
            'words' => 'word up'
        ],
        [
            'numbers' => 15,
            'words' => 'word down'
        ],
        [
            'numbers' => 20,
            'words' => 'all words'
        ],
        [
            'numbers' => 5,
            'words' => 'all words'
        ],
    ];

    public function testFactoryReturnsTabularCollection()
    {
        $coll = Collection::factory($this->testdata[TabularCollection::class]['user']);
        $this->assertInstanceOf(TabularCollection::class, $coll);
        $coll2 = Collection::factory($this->testdata[TabularCollection::class]['profile']);
        $this->assertInstanceOf(TabularCollection::class, $coll2);
    }

    public function testMapTabularCollection()
    {
        $coll = Collection::factory($this->testdata[TabularCollection::class]['user']);
        $func = function($row) {
            $this->assertInstanceOf(AbstractCollection::class, $row);
            return $row->get('email');
        };
        $newcoll = $coll->map($func->bindTo($this));
        $this->assertEquals([
            'ohauck@bahringer.info',
            'larry.emard@pacocha.com',
            'jaylin.mueller@yahoo.com',
            'gfriesen@hotmail.com',
            'verla.ohara@dibbert.com'
        ], $newcoll->toArray());
    }

    public function testContainsWorksWithTabular()
    {
        $coll = Collection::factory($this->testdata[TabularCollection::class]['user']);
        $this->assertTrue($coll->contains('gfriesen@hotmail.com'));
        $this->assertTrue($coll->contains('gfriesen@hotmail.com', 'email'));
        $this->assertFalse($coll->contains('gfriesen@hotmail.com', 'role'));
        $this->assertTrue($coll->contains('gfriesen@hotmail.com', ['id','email','created']));
    }

    public function testContainsWithCallbackWorksWithTabular()
    {
        $coll = Collection::factory($this->testdata[TabularCollection::class]['user']);
        $this->assertTrue($coll->contains(function($val) {
            return $val['is_active'];
        }));
        $this->assertTrue($coll->contains(function($val) {
            return !$val['is_active'];
        }));
        $this->assertTrue($coll->contains(function($val) {
            return is_array($val) && array_key_exists('email', $val);
        }));
        $this->assertFalse($coll->contains(function($val) {
            return is_array($val) && array_key_exists('username', $val);
        }));
    }

    public function testHasColumn()
    {
        $coll = new TabularCollection($this->testdata[TabularCollection::class]['user']);
        $this->assertTrue($coll->hasColumn('email'));
        $this->assertFalse($coll->hasColumn('foobar'));
    }

    public function testGetColumn()
    {
        $coll = new TabularCollection($this->testdata[TabularCollection::class]['user']);
        $this->assertInstanceOf(AbstractCollection::class, $coll->getColumn('email'));
        $this->assertEquals([1,2,3,4,5], $coll->getColumn('id')->toArray());
    }

    public function testAverageColumn()
    {
        $coll = new TabularCollection($this->moretestdata);
        $this->assertEquals(14.6, $coll->average('numbers'));
    }

    public function testSumColumn()
    {
        $coll = new TabularCollection($this->moretestdata);
        $this->assertEquals(146, $coll->sum('numbers'));
    }

    public function testModeColumn()
    {
        $coll = new TabularCollection($this->moretestdata);
        $this->assertEquals(15, $coll->mode('numbers'));
    }

    public function testMedianColumn()
    {
        $coll = new TabularCollection($this->moretestdata);
        $this->assertEquals(15, $coll->median('numbers'));
    }

    public function testMaxColumn()
    {
        $coll = new TabularCollection($this->moretestdata);
        $this->assertEquals(20, $coll->max('numbers'));
    }

    public function testMinColumn()
    {
        $coll = new TabularCollection($this->moretestdata);
        $this->assertEquals(5, $coll->min('numbers'));
    }

    public function testCountsColumn()
    {
        $coll = new TabularCollection($this->moretestdata);
        $this->assertEquals([
            10 => 1,
            17 => 1,
            18 => 1,
            11 => 1,
            20 => 2,
            15 => 3,
            5 => 1
        ], $coll->counts('numbers')->toArray());
    }

    /**
     * @expectedException BadMethodCallException
     * @expectedExceptionMessage Method does not exist: CSVelte\Collection\TabularCollection::nonExistantMethod()
     */
    public function testTabularCollectionThrowsBadMethodCallExceptionOnBadMethodCall()
    {
        $coll = new TabularCollection([
            ['id' => 2, 'name' => 'Luke', 'email' => 'luke.visinoni@gmail.com'],
            ['id' => 3, 'name' => 'Dave', 'email' => 'dave.mason@gmail.com'],
            ['id' => 5, 'name' => 'Joe', 'email' => 'joe.rogan@gmail.com'],
        ]);
        $coll->nonExistantMethod('foo','bar');
    }
}