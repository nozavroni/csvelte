<?php
namespace CSVelteTest;

use \OutOfBoundsException;
use \ArrayIterator;
use \SplFileObject;
use \DateTime;
use \DateInterval;
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

    public function setUp()
    {
        $this->testppl = [
            ['last_name' => 'Visinoni', 'first_name' => 'Luke', 'email' => 'luke@something.com', 'date' => '1986-04-23'],
            ['last_name' => 'Baker', 'first_name' => 'John', 'email' => 'boyjohn@mysite.com', 'date' => '1945-01-21'],
            ['last_name' => 'Wheeler', 'first_name' => 'Jacob', 'email' => 'cobaj@name.com', 'date' => '1990-10-03'],
            ['last_name' => 'Smith', 'first_name' => 'Mark', 'email' => 'mark@ihatescreechingchildren.com', 'date' => '2000-02-14'],
            ['last_name' => 'Jacobs', 'first_name' => 'Jacob', 'email' => 'cobaj@name.com', 'date' => '1994-10-03'],
            ['last_name' => 'Carrell', 'first_name' => 'Lacey', 'email' => 'zlace@boofoo.org', 'date' => '1985-12-31'],
            ['last_name' => 'Stevens', 'first_name' => 'Xavier', 'email' => 'xxxman@gmail.com', 'date' => '1976-11-13'],
            ['last_name' => 'Smithson', 'first_name' => 'Peter', 'email' => 'thepeet@neet.com', 'date' => '1962-03-03'],
            ['last_name' => 'Stimson', 'first_name' => 'Alan', 'email' => 'alan@marsdenfam.com', 'date' => '1959-06-30'],
            ['last_name' => 'Marone', 'first_name' => 'Brutus', 'email' => 'thebrute@czar.com', 'date' => '1950-01-10'],
            ['last_name' => 'White', 'first_name' => 'Carl', 'email' => 'ccc@whitecarl.com', 'date' => '1992-09-04'],
            ['last_name' => 'Black', 'first_name' => 'Gary', 'email' => 'gmeister@pge.gov', 'date' => '1960-07-09'],
            ['last_name' => 'Visinoni', 'first_name' => 'Margaret', 'email' => 'mkultra@dahotness.com', 'date' => '1976-05-11'],
        ];
        return parent::setUp();
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

    public function testSumMethodSumsCollection()
    {
        $coll = new Collection([10,20,30,100,60,80]);
        $this->assertEquals(300, $coll->sum());
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

        // $coll = new Collection(['one','two','three','four','five']);
        // $this->assertEquals('four', $coll->median());

        // @todo Maybe for strings median should work with string length?
        // $coll = new Collection(['hello','world','this','will','do','weird','stuff','yes','it','will']);
        // $this->assertEquals(0, $coll->median());

        $coll = new Collection([1]);
        $this->assertEquals(1, $coll->median());

        $coll = new Collection([1,2]);
        $this->assertEquals(1.5, $coll->median());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testModeWillThrowExceptionForNonnumeric()
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

    public function testCollectionGetKeyAndValAtPosition()
    {
        $coll = new Collection([
            'foo' => 'bar',
            'boo' => 'far',
            'goo' => 'czar',
            'noo' => 'blar',
            'hoo' => 'gnar'
        ]);
        $this->assertTrue($coll->hasPosition(2));
        $this->assertTrue($coll->hasPosition(0));
        $this->assertFalse($coll->hasPosition(5));
        $this->assertEquals("goo", $coll->getKeyAtPosition(2));
        $this->assertEquals("czar", $coll->getValueAtPosition(2));
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testCollectionGetKeyAtPositionThrowsExceptionIfNotPos()
    {
        $coll = new Collection([
            'foo' => 'bar',
            'boo' => 'far',
            'goo' => 'czar',
            'noo' => 'blar',
            'hoo' => 'gnar'
        ]);
        $this->assertEquals("goo", $coll->getKeyAtPosition(5));
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testCollectionGetValueAtPositionThrowsExceptionIfNotPos()
    {
        $coll = new Collection([
            'foo' => 'bar',
            'boo' => 'far',
            'goo' => 'czar',
            'noo' => 'blar',
            'hoo' => 'gnar'
        ]);
        $this->assertEquals("goo", $coll->getValueAtPosition(5));
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
        $coll = $coll->flip();
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
        $this->assertEquals(['foo','boo','goo'], $coll->keys()->toArray());
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
        $coll = $coll->merge(['goo' => 'poo', 'czar' => 'C-ZAR!!!']);
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

    public function testCollectionFrequency()
    {
        $coll = new Collection([',','.','.',';',';',';',';',',',',']);
        $this->assertEquals([
            ',' => 3,
            '.' => 2,
            ';' => 4
        ], $coll->frequency()->toArray());
    }

    /** Two-dimensional Collections **/

    public function test2DCollectionAverage()
    {
        $coll = new Collection([
            [1,2,3,4,50,40,30,10],
            [1,1,1,1, 2, 3, 2, 1],
            [100,200,300,200,300,100,200,200],
            [1.25,336.25,215.5,1,5,50.5,100,100],
            [5,10,15,20,25,30,25,10,25,10],
            [0.1,0,0.2,0.1,0.25,0.1,0.5,0]
        ]);
        $this->assertInternalType("array", $coll->average()->toArray());
        $this->assertEquals([17.5,1.5,200,101.1875,17.5,0.15625], $coll->average()->toArray());
    }

    public function test2DCollectionMax()
    {
        $coll = new Collection([
            [1,2,3,4,50,40,30,10],
            [1,1,1,1, 2, 3, 2, 1],
            [100,200,300,200,300,100,200,200],
            [1.25,336.25,215.5,1,5,50.5,100,100],
            [5,10,15,20,25,30,25,10,25,10],
            [0.1,0,0.2,0.1,0.25,0.1,0.5,0]
        ]);
        $this->assertInternalType("array", $coll->max()->toArray());
        $this->assertEquals([50,3,300,336.25,30,0.5], $coll->max()->toArray());
    }

    public function test2DCollectionMin()
    {
        $coll = new Collection([
            [1,2,3,4,50,40,30,10],
            [1,1,1,1, 2, 3, 2, 1],
            [100,200,300,200,300,100,200,200],
            [1.25,336.25,215.5,1,5,50.5,100,100],
            [5,10,15,20,25,30,25,10,25,10],
            [0.1,0,0.2,0.1,0.25,0.1,0.5,0]
        ]);
        $this->assertInternalType("array", $coll->min()->toArray());
        $this->assertEquals([1,1,100,1,5,0], $coll->min()->toArray());
    }

    public function test2DCollectionMode()
    {
        $coll = new Collection([
            [1,2,3,4,50,40,30,10,1],
            [1,1,1,1, 2, 3, 2, 1],
            [100,200,300,200,300,100,200,200],
            [1.25,336.25,215.5,1,5,50.5,100,100],
            [5,10,15,20,25,30,25,10,25,10],
            [0.1,0,0.2,0.1,0.25,0.1,0.5,0]
        ]);
        $this->assertInternalType("array", $coll->mode()->toArray());
        $this->assertEquals([1,1,200,100,25,0.1], $coll->mode()->toArray());
    }

    public function test2DCollectionMedian()
    {
        $coll = new Collection([
            [1,2,3,4,50,40,30,10,1], // 1,1,2,3,4,10,30,40,50
            [1,1,1,1, 2, 3, 2, 1], // 1,1,1,1,1,2,2,3
            [100,200,300,200,300,100,200,200], // 100, 100, 200, 200, 200, 200, 300, 300
            [1.25,336.25,215.5,1,5,50.5,100,100], // 1, 1.25, 5, 50.5, 100, 100, 215.5, 336.25,
            [5,10,15,20,25,30,25,10,25,10], // 5, 10, 15,
            [0.1,0,0.2,0.1,0.25,0.1,0.5,0]
        ]);
        $this->assertInternalType("array", $coll->median()->toArray());
        $this->assertEquals([4,1,200,75.25,17.5,0.1], $coll->median()->toArray());
    }

    public function test2DCollectionSum()
    {
        $coll = new Collection([
            [1,2,3,4,50,40,30,10,1], // 1,1,2,3,4,10,30,40,50
            [1,1,1,1, 2, 3, 2, 1], // 1,1,1,1,1,2,2,3
            [100,200,300,200,300,100,200,200], // 100, 100, 200, 200, 200, 200, 300, 300
            [1.25,336.25,215.5,1,5,50.5,100,100], // 1, 1.25, 5, 50.5, 100, 100, 215.5, 336.25,
            [5,10,15,20,25,30,25,10,25,10], // 5, 10, 15,
            [0.1,0,0.2,0.1,0.25,0.1,0.5,0]
        ]);
        $this->assertInternalType("array", $coll->sum()->toArray());
        $this->assertEquals([141,12,1600,809.5,175,1.25], $coll->sum()->toArray());
    }

    public function test2DCollectionCount()
    {
        $coll = new Collection([
            [1,2,3,4,50,40,30,10,1], // 1,1,2,3,4,10,30,40,50
            [1,1,1,1,1], // 1,1,1,1,1,2,2,3
            [100,200,300,200,300,100,200,200], // 100, 100, 200, 200, 200, 200, 300, 300
            [1.25,336.25,215.5,1,5,50.5,100,100], // 1, 1.25, 5, 50.5, 100, 100, 215.5, 336.25,
            [5,10,15,10], // 5, 10, 15,
            [0.1]
        ]);
        $this->assertInternalType("array", $coll->count(true)->toArray());
        $this->assertEquals([9,5,8,8,4,1], $coll->count(true)->toArray());
    }

    public function test2DCollectionFrequency()
    {
        $coll = new Collection([
            [1,2,3,4,50,40,30,10,1],
            [1,1,1,1,1],
            [100,200,300,200,300,100,200,200],
            ['a','a','a','a','b','b','c','d','e','e','e','e','e','e','e','f']
        ]);
        $this->assertInternalType("array", $coll->frequency()->toArray());
        $this->assertEquals([[
            1 => 2,
            2 => 1,
            3 => 1,
            4 => 1,
            50 => 1,
            40 => 1,
            30 => 1,
            10 => 1
        ],[
            1 => 5
        ],[
            100 => 2,
            200 => 4,
            300 => 2
        ],[
            'a' => 4,
            'b' => 2,
            'c' => 1,
            'd' => 1,
            'e' => 7,
            'f' => 1
        ]], $coll->frequency()->toArray());
    }

    public function testSort()
    {
        $coll = new Collection($data = ['b','a','c','b','cool' => 'f','d','ad','az','ba','aa','Za','aZ','AZ','ZA']);
        $coll = $coll->sort();
        $dcopy = $data;
        natcasesort($dcopy);
        $this->assertEquals($dcopy, $coll->toArray());

        $coll2 = new Collection($data);
        $coll2 = $coll2->sort(function($a, $b){
            $a = strtolower($a);
            $b = strtolower($b);
            if ($a == $b) return 0;
            if (strlen($a) > strlen($b)) return 1;
            if (strlen($b) > strlen($a)) return -1;
            return ($a > $b) ? 1 : -1;
        });
        $this->assertEquals([
            1 => 'a', 0 => 'b', 3 => 'b', 2 => 'c', 4 => 'd','cool' => 'f', 8 => 'aa',
            5 => 'ad', 11 => 'AZ', 6 => 'az', 10 => 'aZ', 7 => 'ba', 12 => 'ZA', 9 => 'Za'
            ],$coll2->toArray()
        );

        $coll = new Collection($data = ['b','a','c','b','cool' => 'f','d','ad','az','ba','aa','Za','aZ','AZ','ZA']);
        $coll = $coll->sort('strnatcasecmp', false);
        $dcopy = $data;
        natcasesort($dcopy);
        $this->assertEquals(array_values($dcopy), $coll->toArray());
    }

    public function testReverse()
    {
        $coll = new Collection($data = ['b','a','c','b','cool' => 'f','d','ad','az','ba','aa','Za','aZ','AZ','ZA']);
        $this->assertEquals($data, $coll->toArray());
        $this->assertEquals(array_reverse($data, true), $coll->reverse()->toArray());
        $this->assertEquals(array_reverse($data, false), $coll->reverse(false)->toArray());
    }

    public function testOrderBy()
    {
        $coll = new Collection([
            ['name' => 'Luke', 'email' => 'luke@something.com', 'date' => '1986-04-23'],
            ['name' => 'John', 'email' => 'boyjohn@mysite.com', 'date' => '1945-01-21'],
            ['name' => 'Jacob', 'email' => 'cobaj@name.com', 'date' => '1992-10-03'],
            ['name' => 'Mark', 'email' => 'mark@ihatescreechingchildren.com', 'date' => '2000-02-14'],
        ]);
        $this->assertEquals([
            2 => ['name' => 'Jacob', 'email' => 'cobaj@name.com', 'date' => '1992-10-03'],
            1 => ['name' => 'John', 'email' => 'boyjohn@mysite.com', 'date' => '1945-01-21'],
            0 => ['name' => 'Luke', 'email' => 'luke@something.com', 'date' => '1986-04-23'],
            3 => ['name' => 'Mark', 'email' => 'mark@ihatescreechingchildren.com', 'date' => '2000-02-14'],
        ], $coll->orderBy('name')->toArray());
        $this->assertEquals([
            1 => ['name' => 'John', 'email' => 'boyjohn@mysite.com', 'date' => '1945-01-21'],
            0 => ['name' => 'Luke', 'email' => 'luke@something.com', 'date' => '1986-04-23'],
            2 => ['name' => 'Jacob', 'email' => 'cobaj@name.com', 'date' => '1992-10-03'],
            3 => ['name' => 'Mark', 'email' => 'mark@ihatescreechingchildren.com', 'date' => '2000-02-14'],
        ], $coll->orderBy('date')->toArray());
        $this->assertEquals([
            2 => ['name' => 'Jacob', 'email' => 'cobaj@name.com', 'date' => '1992-10-03'],
            3 => ['name' => 'Mark', 'email' => 'mark@ihatescreechingchildren.com', 'date' => '2000-02-14'],
            1 => ['name' => 'John', 'email' => 'boyjohn@mysite.com', 'date' => '1945-01-21'],
            0 => ['name' => 'Luke', 'email' => 'luke@something.com', 'date' => '1986-04-23'],
        ], $coll->orderBy('date', $cmpday = function($a, $b) {
            $adt = new \DateTime($a);
            $bdt = new \DateTime($b);
            return ($adt->format('j') - $bdt->format('j'));
        })->toArray());
        $this->assertEquals([
            ['name' => 'Jacob', 'email' => 'cobaj@name.com', 'date' => '1992-10-03'],
            ['name' => 'Mark', 'email' => 'mark@ihatescreechingchildren.com', 'date' => '2000-02-14'],
            ['name' => 'John', 'email' => 'boyjohn@mysite.com', 'date' => '1945-01-21'],
            ['name' => 'Luke', 'email' => 'luke@something.com', 'date' => '1986-04-23'],
        ], $coll->orderBy('date', $cmpday, false)->toArray());
    }

    public function testWhere()
    {
        $coll = new Collection($this->testppl);
        $this->assertEquals([
                 2 => ['last_name' => 'Wheeler', 'first_name' => 'Jacob', 'email' => 'cobaj@name.com', 'date' => '1990-10-03'],
                 3 => ['last_name' => 'Smith', 'first_name' => 'Mark', 'email' => 'mark@ihatescreechingchildren.com', 'date' => '2000-02-14'],
                 4 => ['last_name' => 'Jacobs', 'first_name' => 'Jacob', 'email' => 'cobaj@name.com', 'date' => '1994-10-03'],
                10 => ['last_name' => 'White', 'first_name' => 'Carl', 'email' => 'ccc@whitecarl.com', 'date' => '1992-09-04']
            ],
            $coll->where('date', function($date, $key) {
                $dt = new \DateTime($date);
                $dtdiff = $dt->diff((new \DateTime()));
                return ($dtdiff->days < 10000);
            })->toArray(),
            "Assert that Collection::where can accept an anonymous function to do custom where values"
        );
        $this->assertEquals([
                0 => ['last_name' => 'Visinoni', 'first_name' => 'Luke', 'email' => 'luke@something.com', 'date' => '1986-04-23'],
                12 => ['last_name' => 'Visinoni', 'first_name' => 'Margaret', 'email' => 'mkultra@dahotness.com', 'date' => '1976-05-11'],
            ],
            $coll->where('last_name', 'Visinoni')->toArray(),
            "Assert that Collection::where with value rather than anonymous function defaults to == (equality) comparison"
        );

    }

    public function testWhereAcceptsThirdArgumentThatDictatesWhatTypeOfEqualityComparisonToUse()
    {
        $coll = new Collection($this->testppl);
        $this->assertEquals([
                 2 => ['last_name' => 'Wheeler', 'first_name' => 'Jacob', 'email' => 'cobaj@name.com', 'date' => '1990-10-03'],
                 3 => ['last_name' => 'Smith', 'first_name' => 'Mark', 'email' => 'mark@ihatescreechingchildren.com', 'date' => '2000-02-14'],
                 4 => ['last_name' => 'Jacobs', 'first_name' => 'Jacob', 'email' => 'cobaj@name.com', 'date' => '1994-10-03'],
                10 => ['last_name' => 'White', 'first_name' => 'Carl', 'email' => 'ccc@whitecarl.com', 'date' => '1992-09-04']
            ],
            $coll->where('date', '1990-01-01', Collection::WHERE_GT)->toArray(),
            'Assert that Collection::where can accept > (greater than) as third argument (comparison/equality type)'
        );
        $this->assertEquals([
                0 => ['last_name' => 'Visinoni', 'first_name' => 'Luke', 'email' => 'luke@something.com', 'date' => '1986-04-23'],
                1 => ['last_name' => 'Baker', 'first_name' => 'John', 'email' => 'boyjohn@mysite.com', 'date' => '1945-01-21'],
                5 => ['last_name' => 'Carrell', 'first_name' => 'Lacey', 'email' => 'zlace@boofoo.org', 'date' => '1985-12-31'],
                6 => ['last_name' => 'Stevens', 'first_name' => 'Xavier', 'email' => 'xxxman@gmail.com', 'date' => '1976-11-13'],
                7 => ['last_name' => 'Smithson', 'first_name' => 'Peter', 'email' => 'thepeet@neet.com', 'date' => '1962-03-03'],
                8 => ['last_name' => 'Stimson', 'first_name' => 'Alan', 'email' => 'alan@marsdenfam.com', 'date' => '1959-06-30'],
                9 => ['last_name' => 'Marone', 'first_name' => 'Brutus', 'email' => 'thebrute@czar.com', 'date' => '1950-01-10'],
                11 => ['last_name' => 'Black', 'first_name' => 'Gary', 'email' => 'gmeister@pge.gov', 'date' => '1960-07-09'],
                12 => ['last_name' => 'Visinoni', 'first_name' => 'Margaret', 'email' => 'mkultra@dahotness.com', 'date' => '1976-05-11'],
            ],
            $coll->where('date', '1990-01-01', Collection::WHERE_LTE)->toArray(),
            'Assert that Collection::where can accept <= (less than or equal to) as third argument (comparison/equality type)'
        );
        $this->assertEquals([
                0 => ['last_name' => 'Visinoni', 'first_name' => 'Luke', 'email' => 'luke@something.com', 'date' => '1986-04-23'],
                12 => ['last_name' => 'Visinoni', 'first_name' => 'Margaret', 'email' => 'mkultra@dahotness.com', 'date' => '1976-05-11'],
            ],
            $coll->where('last_name', 'visinoni', Collection::WHERE_LIKE)->toArray(),
            'Assert that Collection::where can accept <= (less than or equal to) as third argument (comparison/equality type)'
        );
        $this->assertEquals([2,4], $coll->where('first_name', 'jacob', Collection::WHERE_LIKE)->keys()->toArray());
        $this->assertEquals([0,1,3,5,6,7,8,9,10,11,12], $coll->where('first_name', 'jacob', Collection::WHERE_NLIKE)->keys()->toArray());
        $this->assertEquals([0,1,2,3,4,5,6,7,8,9,10,11,12], $coll->where('first_name', 'jacob', Collection::WHERE_NEQ)->keys()->toArray());

    }

    public function testWhereWithNonStringAndRegexComparisons()
    {
        $table = [
            0 => ['object' => new DateTime, 'mixed' => 1, 'integer' => 1, 'string' => 'one', 'match' => '14-xx-1235157S'],
            1 => ['object' => new stdClass, 'mixed' => 'nuts', 'integer' => 6510, 'string' => 'string', 'match' => '_+_--+_----+'],
            2 => ['object' => new ArrayIterator([]), 'mixed' => -20, 'integer' => 0, 'string' => 'zero', 'match' => 'ak-47'],
            3 => ['object' => new DateTime('10-10-10 10:10:10'), 'mixed' => 50.15, 'integer' => -78, 'string' => 'i am a string', 'match' => '__something__'],
            4 => ['object' => new stdClass(['a' => 'b', 'b' => 1]), 'mixed' => new stdClass, 'integer' => 0, 'string' => 'one', 'match' => '15-ex-458x'],
            5 => ['object' => null, 'mixed' => null, 'integer' => null, 'string' => null, 'match' => null],
            6 => ['object' => new DateInterval('P10000D'), 'mixed' => 10000, 'integer' => 10000, 'string' => 'ten thousand', 'match' => '00-aa-10000d'],
        ];
        $coll = new Collection($table);
        $this->assertEquals([0,3], $coll->where('object', DateTime::class, 'instanceof')->keys()->toArray());
        $this->assertEquals([1,2,4,5,6], $coll->where('object', DateTime::class, '!instanceof')->keys()->toArray());
        $this->assertEquals([4], $coll->where('mixed', 'stdClass', 'instanceof')->keys()->toArray());
        $this->assertEquals([0,2,6], $coll->where('mixed', 'integer', 'typeof')->keys()->toArray());
        $this->assertEquals([1,3,4,5], $coll->where('mixed', 'integer', '!typeof')->keys()->toArray());
        $this->assertEquals([5], $coll->where('mixed', 'null', 'typeof')->keys()->toArray());
        $this->assertEquals([0,4,6], $coll->where('match', '/[0-9]{2}-[a-z]{2}-[0-9]+[a-z]/i', 'match')->keys()->toArray());
        $this->assertEquals([1,2,3,5], $coll->where('match', '/[0-9]{2}-[a-z]{2}-[0-9]+[a-z]/i', '!match')->keys()->toArray());
        $this->assertEquals([1], $coll->where('match', '~([_-][+-])+~', 'match')->keys()->toArray());
        $this->assertEquals([2,4], $coll->where('integer', 0, '===')->keys()->toArray());
        $this->assertEquals([], $coll->where('object', new DateTime, '===')->keys()->toArray());
        $this->assertEquals([0,1,2,3,4,5,6], $coll->where('object', new ArrayIterator([]), '!==')->keys()->toArray());
    }

}
