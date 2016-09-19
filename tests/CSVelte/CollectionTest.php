<?php
namespace CSVelteTest;

use \OutOfBoundsException;
use \ArrayIterator;
use \SplFileObject;
use \DateTime;
use \stdClass;
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

    protected function getMixedNuts()
    {
        return [
            'foo' => 'bar',
            'boo' => 'far',
            'goo' => 'czar',
            'doo' => 'dar',
            'iter' => new ArrayIterator([1,2,3]),
            'obj' => new stdClass('1,2,3'),
            'moo' => 'mar',
            'too' => 'tootoo',
            'roo' => 1267,
            'asd' => [],
            'fls' => false,
            'zero' => 0,
            ''    => 'blankee',
             1000 => 'intkey',
                1 => 'nero',
            'que' => new DateTime()
        ];
    }

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

    public function testCollectionHasReturnsTrueIfRequestedKeyInCollectionKeys()
    {
        $coll = new Collection([
            'foo' => 'bar',
            'boo' => 'far',
            'goo' => 'czar'
        ]);
        $this->assertTrue($coll->has('foo'));
        $this->assertFalse($coll->has('poo'));
    }

    public function testCollectionContainsReturnsTrueIfRequestedValueInCollection()
    {
        $coll = new Collection([
            'foo' => 'bar',
            'boo' => 'far',
            'goo' => 'czar'
        ]);
        $this->assertTrue($coll->contains('bar'));
        $this->assertFalse($coll->contains('tar'));

        // can also check key
        $this->assertTrue($coll->contains('bar', 'foo'), "Ensure Container::contains() can pass a second param for key. ");
        $this->assertFalse($coll->contains('far', 'poo'));

        // can also accept a callable to determine if collection contains user-specified criteria
        $this->assertTrue($coll->contains(function($val, $key) {
            return strlen($val) > 3;
        }));
        $this->assertFalse($coll->contains(function($val, $key) {
            return strlen($val) < 3;
        }));
        $this->assertFalse($coll->contains(function($val, $key) {
            return $val instanceof Iterator;
        }));
    }

    public function testCollectionCount()
    {
        $coll = new Collection([
            'foo' => 'bar',
            'boo' => 'far',
            'goo' => 'czar'
        ]);
        $this->assertEquals(3, $coll->count());
    }

    public function testCollectionEachReturnsFalseToStopLoop()
    {
        $coll = new Collection([
            'foo' => 'bar',
            'boo' => 'far',
            'goo' => 'czar',
            'doo' => 'dar',
            'moo' => 'mar'
        ]);
        $string = '';
        $coll->each(function($val) use (&$string) {
            $string .= $val;
            if (strlen($val) > 3) return false;
        });
        $this->assertEquals('barfarczar', $string);
    }

    public function testCollectionFilterUsingCallable()
    {
        $coll = new Collection($this->getMixedNuts());
        $this->assertEquals(16, $coll->count());
        $coll->filter(function($val, $key) {
            return !is_object($val);
        });
        $this->assertEquals(13, count($coll));

        $coll->filter(function($val, $key){ return !is_numeric($key); });
        $this->assertEquals(11, count($coll));
        $coll->each(function($val, $key){
            $this->assertTrue(!is_object($val));
            $this->assertTrue(!is_numeric($key));
        });
    }

    public function testFirstMethodReturnsFirstElementToPassTruthTest()
    {
        $coll = new Collection($this->getMixedNuts());
        $elem = $coll->first(function($val, $key){
            return is_object($val);
        });
        $this->assertInternalType('object', $elem);
        $this->assertInstanceOf(ArrayIterator::class, $elem);
    }

    public function testLastMethodReturnsLastElementToPassTruthTest()
    {
        $coll = new Collection($this->getMixedNuts());
        $elem = $coll->last(function($val, $key){
            return is_object($val);
        });
        $this->assertInternalType('object', $elem);
        $this->assertInstanceOf(DateTime::class, $elem);
    }

    public function testFlipReversesKeysAndValues()
    {
        $coll = new Collection([
            'foo' => 'bar',
            'boo' => 'far',
            'goo' => 'czar'
        ]);
        $this->assertTrue($coll->contains('czar', 'goo'));
        $this->assertFalse($coll->contains('goo','czar'));
        $coll->flip();
        $this->assertTrue($coll->contains('goo','czar'));
        $this->assertFalse($coll->contains('czar', 'goo'));
    }

    public function testUnsetByKey()
    {
        $coll = new Collection([
            'foo' => 'bar',
            'boo' => 'far',
            'goo' => 'czar'
        ]);
        $this->assertTrue($coll->contains('czar'));
        $coll->offsetUnset('goo');
        $this->assertFalse($coll->contains('czar'));
        unset($coll['foo']);
        $this->assertFalse($coll->contains('bar'));
    }

    public function testJoinUsingComma()
    {
        $coll = new Collection([
            'foo' => 'bar',
            'boo' => 'far',
            'goo' => 'czar'
        ]);
        $this->assertEquals('bar,far,czar', $coll->join(','));
    }

    public function testKeysMethodReturnsDataKeys()
    {
        $coll = new Collection([
            'foo' => 'bar',
            'boo' => 'far',
            'goo' => 'czar'
        ]);
        $this->assertEquals(['foo','boo','goo'], $coll->keys());
    }

    public function testIsEmptyReturnsTrueIfCollectionIsEmpty()
    {
        $coll = new Collection();
        $this->assertTrue($coll->isEmpty());
    }

    public function testMinReturnsSmallestElemAndMaxReturnsLargestElem()
    {
        $coll = new Collection([104,906,532,123,116,216,366,34,783,455,449]);
        $this->assertEquals(34, $coll->min());
        $this->assertEquals(906, $coll->max());
    }

    public function testMergeOverwritesExistingData()
    {
        $coll = new Collection([
            'foo' => 'bar',
            'boo' => 'far',
            'goo' => 'czar'
        ]);
        $coll->merge(['goo' => 'poo', 'czar' => 'C-ZAR!!!']);
        $this->assertEquals(['foo' => 'bar', 'boo' => 'far', 'goo' => 'poo', 'czar' => 'C-ZAR!!!'], $coll->toArray());
    }

    public function testValueAcceptsCallableAndImmediatelyGetsReturnValue()
    {
        $coll = new Collection([1,50,23,45,86,101]);
        $this->assertEquals(1, $coll->value(function($c){
            return $c->min();
        }));
        $this->assertEquals(101, $coll->value(function($c){
            return $c->max();
        }));
        $this->assertEquals(51, $coll->value(function($c){
            return $c->average();
        }));
    }

    public function testCollectionCallAsFunctionCallsInvoke()
    {
        $coll = new Collection([1,50,23,45,86,101]);
        $coll2 = $coll(function($val) {
            return $val . '+';
        });
        $this->assertEquals([1 . '+', 50 . '+', 23 . '+', 45 . '+', 86 . '+', 101 . '+'], $coll2->toArray());
    }

    public function testCollectionUniqueRemovesDuplicates()
    {
        $coll = new Collection($arr = [1,1,1,1,2,2,3,4,5,66,7,8,9,1,2,2,3,4,6,66]);
        $this->assertEquals($arr, $coll->toArray());
        $this->assertEquals([0 => 1, 4 => 2, 6 => 3, 7 => 4, 8 => 5, 9 => 66, 10 => 7, 11 => 8, 12 => 9, 18 => 6], $coll->unique()->toArray());
    }
}
