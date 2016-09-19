<?php
namespace CSVelteTest;

use \OutOfBoundsException;
use \SplFileObject;
use CSVelte\Collection;

/**
 * CSVelte Collection tests
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class CollectionTest extends UnitTestCase
{
    protected $dummydata = array(
        array('foo','bar','baz'),
        array('1','luke','visinoni'),
        array('2','margaret','kelly'),
        array('3','jerry','rafferty'),
        array('5','larry','stevens'),
        array('7','pete','pippen'),
        array('10','greg','milton'),
    );

    protected function getDummyData()
    {
        return $this->dummydata;
    }

    protected function getDummyTabularData()
    {
        $arr = [];
        $data = $this->dummydata;
        $header = array_shift($data);
        foreach ($data as $line => $row) {
            $hrow = array_combine($header, $row);
            $arr[] = $hrow;
        }
        return $arr;
    }

    protected function getDummyData1D()
    {
        $arr = [];
        foreach($this->getDummyData() as $line => $row) {
            foreach ($row as $col => $field) {
                $arr[] = $field;
            }
        }
        return $arr;
    }

    public function testInstantiateCollection()
    {
        $coll = new Collection($tdata = $this->getDummyTabularData());
        $this->assertEquals($tdata, $coll->toArray());
    }

    public function testInstantiateCollectionWithIteratorUsingSplFileObject()
    {
        $iter = new SplFileObject($this->getFilePathFor('veryShort'));
        $coll = new Collection($iter);
        $this->assertEquals("foo,bar,baz\n", $coll->get(0));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInstantiateWithInvalidArg()
    {
        $coll = new Collection(false);
    }

    public function testCollectionAccessors()
    {
        $coll = new Collection([
            'foo' => 'bar',
            'boo' => 'far',
            'goo' => 'czar'
        ]);
        $this->assertEquals('czar', $coll->get('goo'));
        $this->assertEquals('C-ZAR!!', $coll->get('poo', 'C-ZAR!!'));
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testCollectionAccessorGetNonExistantWithThrowsParamTrue()
    {
        $coll = new Collection([
            'foo' => 'bar',
            'boo' => 'far',
            'goo' => 'czar'
        ]);
        $this->assertEquals("default", $coll->get('nonexist', "default"));
        $this->assertEquals("default", $coll->get('nonexist', "default", true));
    }

    public function testAverageMethodAveragesCollection()
    {
        $coll = new Collection([10,20,30,100,60,80]);
        $this->assertEquals(50, $coll->average());
    }

    public function testModeMethodReturnsCollectionMode()
    {
        $coll = new Collection([10,20,30,100,60,80,10,20,100,10,50,40,10,20,50,60,80]);
        $this->assertEquals(10, $coll->mode());
    }

    public function testMedianMethodReturnsCollectionMedian()
    {
        $coll = new Collection([1,10,20,30,100,60,80,10,20,100,10,50,40,10,20,50,60,80]);
        $this->assertEquals(35, $coll->median());

        $coll = new Collection([1,20,300,4000]);
        $this->assertEquals(160, $coll->median());

        $coll = new Collection(['one','two','three','four','five']);
        $this->assertEquals('four', $coll->median());

        // @todo Maybe for strings median should work with string length?
        $coll = new Collection(['hello','world','this','will','do','weird','stuff','yes','it','will']);
        $this->assertEquals(0, $coll->median());

        $coll = new Collection([1]);
        $this->assertEquals(1, $coll->median());

        $coll = new Collection([1,2]);
        $this->assertEquals(1.5, $coll->median());
    }

    public function testModeMethodReturnsCollectionModeWithNonDigits()
    {
        $coll = new Collection(['i','like','to','eat','ham','i']);
        $this->assertEquals('i', $coll->mode());
    }

    public function testMapReturnsANewCollectionContainingValuesAfterCallback()
    {
        $coll = new Collection([0,1,2,3,4,5,6,7,8,9]);
        $coll2 = $coll->map(function($val){
            return $val + 1;
        });
        $this->assertInstanceOf(Collection::class, $coll2);
        $this->assertEquals([1,2,3,4,5,6,7,8,9,10], $coll2->toArray());
    }

    public function testPairsReturnsArrayOfKeyValuePairs()
    {
        $coll = new Collection([
            'foo' => 'bar',
            'boo' => 'far',
            'goo' => 'czar'
        ]);
        $this->assertEquals([
            ['foo','bar'],
            ['boo','far'],
            ['goo','czar']
        ], $coll->pairs());

        $coll = new Collection([
            'foo' => 'bar',
            'boo' => 'far',
            'goo' => 'czar'
        ]);
        $this->assertEquals([
            ['foo' => 'bar'],
            ['boo' => 'far'],
            ['goo' => 'czar']
        ], $coll->pairs(true));
    }

    public function testCollectionWalkCallbackModifyInPlace()
    {
        $coll = new Collection([1,2,3,4,5,6,7,8,9,0]);
        $coll->walk(function (&$value, $key, $udata) {
            if ($key %2 == 0) $value++;
            else $value--;
            $value .= $udata['extra_context'];
        }, [
            'extra_context' => 'foobar'
        ]);
        $this->assertEquals([
            '2foobar',
            '1foobar',
            '4foobar',
            '3foobar',
            '6foobar',
            '5foobar',
            '8foobar',
            '7foobar',
            '10foobar',
            '-1foobar'
        ], $coll->toArray());
    }
}
