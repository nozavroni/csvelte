<?php
namespace CSVelteTest\Collection;

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

}