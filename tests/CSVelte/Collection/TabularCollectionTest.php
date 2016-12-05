<?php
namespace CSVelteTest\Collection;

use CSVelte\Collection\AbstractCollection;
use CSVelte\Collection\Collection;
use CSVelte\Collection\MultiCollection;

use CSVelte\Collection\TabularCollection;
use function CSVelte\is_traversable;

class TabularCollectionTest extends AbstractCollectionTest
{
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
}