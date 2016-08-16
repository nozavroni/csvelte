<?php
/**
 * CSVelte\Utils Tests.
 *
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
use CSVelte\Utils;
use PHPUnit\Framework\TestCase;

class UtilsTest extends TestCase
{
    protected $dummydata = [
        ['foo', 'bar', 'baz'],
        ['1', 'luke', 'visinoni'],
        ['2', 'margaret', 'kelly'],
        ['3', 'jerry', 'rafferty'],
        ['5', 'larry', 'stevens'],
        ['7', 'pete', 'pippen'],
        ['10', 'greg', 'milton'],
    ];

    protected $keyed = [
        'foo' => 'bar',
        'boo' => 'baz',
        'too' => 'maz',
    ];

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
        $expected = [
            ['foo', 'bar'],
            ['boo', 'baz'],
            ['too', 'maz'],
        ];
        $this->assertEquals($expected, Utils::array_items($this->keyed));
    }

    public function testAverageMethodGetsArrayAverage()
    {
        $arr = [2, 3, 4, 5, 6, 7, 8];
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
        Utils::array_average([[3, 2, 1], 10, [1, 2, 3]]);
    }

    public function testArrayAverageAveragesAll()
    {
        $matrix = [
            [2, 3, 4, 5, 6, 7, 8],
            [10, 3, 4, 9, 6, 7, 3],
            [19, 19, 15, 15, 16, 17, 18],
            [21, 13, 46, 106, 69, 28, 81],
        ];
        $this->assertEquals([5, 6, 17, 52], Utils::array_average($matrix));
    }

    public function testModeMethodGetsArrayMode()
    {
        $arr = [1, 1, 1, 2, 1, 2, 5, 3, 2, 1, 2, 4, 2, 2, 1, 2, 2, 1, 2, 2, 3, 2];
        $this->assertEquals(2, Utils::mode($arr));
    }

    public function testArrayModeMethodGetsArrayModeArray()
    {
        $arr = [
            [1, 1, 1, 2, 1, 2, 5, 3, 2, 1, 2, 4, 2, 2, 1, 2, 2, 1, 2, 2, 3, 2],
            [1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 1],
            [1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 2, 2, 2, 2, 2, 1],
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 1, 2, 3, 4, 5, 6, 7, 8, 9, 1, 2, 3, 1],
        ];
        $this->assertEquals([2, 1, 1, 1], Utils::array_mode($arr));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testArrayModeMethodThrowsExceptionIfNotPassedArrayOfArrays()
    {
        Utils::array_mode([[3, 2, 1], 10, [1, 2, 3]]);
    }

    public function testStringMapAltersStringCorrectly()
    {
        $string = 'aaabbbccc';
        $expected = '1-1-1-2-2-2-3-3-3-';
        $this->assertEquals($expected, Utils::string_map($string, function ($chr) {
            $trans = ['a' => 1, 'b' => 2, 'c' => '3'];

            return $trans[$chr].'-';
        }));
    }
}
