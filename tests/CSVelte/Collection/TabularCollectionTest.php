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
}