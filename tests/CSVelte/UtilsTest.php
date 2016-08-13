<?php
/**
 * CSVelte\Utils Tests
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
use PHPUnit\Framework\TestCase;
use CSVelte\Utils;

class UtilsTest extends TestCase
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

    protected $keyed = array(
        'foo' => 'bar',
        'boo' => 'baz',
        'too' => 'maz'
    );

    public function setUp()
    {

    }

    public function tearDown()
    {

    }

    public function testArrayGetGetsValueOrDefault()
    {
        $this->assertEquals('bar', Utils::array_get($this->keyed, 'foo'), 'Ensure array_get fetches value');
        $this->assertEquals('bleh!', Utils::array_get($this->keyed, 'roo', 'bleh!'), 'Ensure array_get returns default for missing value');
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testArrayGetThrowsExceptionOnMissingValue()
    {
        Utils::array_get($this->keyed, 'poo', null, true);
    }

    public function testArrayItemsReturnsKeyValuePairs()
    {
        $expected = array(
            array('foo','bar'),
            array('boo','baz'),
            array('too','maz'),
        );
        $this->assertEquals($expected, Utils::array_items($this->keyed));
    }

    public function testAverageMethodGetsArrayAverage()
    {
        $arr = array(2, 3, 4, 5, 6, 7, 8);
        $this->assertEquals(5, Utils::average($arr));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAverageMethodThrowsExceptionIfNotPassedArray()
    {
        Utils::average(10);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testArrayAverageMethodThrowsExceptionIfNotPassedArrayOfArrays()
    {
        Utils::array_average(array(array(3,2,1), 10, array(1,2,3)));
    }

    public function testArrayAverageAveragesAll()
    {
        $matrix = array(
            array(2, 3, 4, 5, 6, 7, 8),
            array(10, 3, 4, 9, 6, 7, 3),
            array(19, 19, 15, 15, 16, 17, 18),
            array(21, 13, 46, 106, 69, 28, 81)
        );
        $this->assertEquals(array(5,6,17,52), Utils::array_average($matrix));
    }

    public function testModeMethodGetsArrayMode()
    {
        $arr = array(1,1,1,2,1,2,5,3,2,1,2,4,2,2,1,2,2,1,2,2,3,2);
        $this->assertEquals(2, Utils::mode($arr));
    }

    public function testArrayModeMethodGetsArrayModeArray()
    {
        $arr = array(
            array(1,1,1,2,1,2,5,3,2,1,2,4,2,2,1,2,2,1,2,2,3,2),
            array(1,2,1,2,1,2,1,2,1,2,1,2,1,2,1,2,1,2,1,2,1,1),
            array(1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,2,2,2,2,2,1),
            array(1,2,3,4,5,6,7,8,9,1,2,3,4,5,6,7,8,9,1,2,3,1),
        );
        $this->assertEquals(array(2,1,1,1), Utils::array_mode($arr));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testArrayModeMethodThrowsExceptionIfNotPassedArrayOfArrays()
    {
        Utils::array_mode(array(array(3,2,1), 10, array(1,2,3)));
    }

    public function testStringMapAltersStringCorrectly()
    {
        $string = "aaabbbccc";
        $expected = "1-1-1-2-2-2-3-3-3-";
        $this->assertEquals($expected, Utils::string_map($string, function($chr) {
            $trans = array('a' => 1, 'b' => 2, 'c' => '3');
            return $trans[$chr] . '-';
        }));
    }

}
