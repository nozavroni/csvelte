<?php
/**
 * CSVelte: Slender, elegant CSV for PHP
 *
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @version   v${CSVELTE_DEV_VERSION}
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace CSVelteTest\Collection;

use CSVelte\Collection\CharCollection;
use CSVelteTest\UnitTestCase;

class CharCollectionTest extends UnitTestCase
{
    public function testCharCollectionAcceptsString()
    {
        $chars = new CharCollection($exp = 'A collection of chars');
        $this->assertEquals($exp, (string) $chars);
        $this->assertEquals(str_split($exp), $chars->toArray());
    }

    public function testContainsReturnsTrueIFCharInCollection()
    {
        $chars = new CharCollection($exp = 'A collection of chars');
        $this->assertTrue($chars->contains('c'));
        $this->assertFalse($chars->contains('Z'));
    }

    public function testMapRunsFuncForEveryChar()
    {
        $chars = new CharCollection($exp = 'A collection of chars');
        $nl = $chars->map(function($char){
            if ($char == ' ') {
                return "\n";
            }
            return $char;
        });
        $this->assertEquals("A\ncollection\nof\nchars", (string) $nl);
    }

    public function testCountReturnsCharCount()
    {
        $chars = new CharCollection($exp = 'A character set');
        $this->assertEquals(strlen($exp), $chars->count());
        $this->assertEquals(strlen($exp), count($chars));
    }

    public function testHasReturnsTrueIfOffsetExists()
    {
        $chars = new CharCollection($exp = 'A character set');
        $this->assertTrue($chars->has(5));
        $this->assertFalse($chars->has(50));
        $this->assertFalse($chars->has('c'));
    }

    /**
     * @todo add method to grab a slice of a collection
     */
    public function testGetReturnsCharacterAtGivenOffset()
    {
        $chars = new CharCollection($exp = 'A character set');
        $this->assertEquals('r', $chars->get(5));
        $this->assertEquals('A', $chars->get(0));
        $this->assertNull($chars->get(15));
    }

    public function testSetReplacesCharacterAtGivenOffset()
    {
        $chars = new CharCollection($exp = 'A character set');
        $chars->set('5', 'p');
        $this->assertEquals('p', $chars->get(5));
        // @todo I'm not sure this is the behavior I want
        $chars->set('3', 'poo');
        $this->assertEquals('poo', $chars->get(3));
        $this->assertEquals('A cpooapacter set', (string) $chars);
    }

    public function testDeleteRemovesCharacterAtGivenOffset()
    {
        $chars = new CharCollection($exp = 'A character set');
        $chars->delete(5);
        $this->assertEquals('A chaacter set', (string) $chars);
    }

    /**
     * @todo Merge and a few other methods aren't really relevant to a character set. Probably should remove them from abstract collection?
     */
//    public function testMergeDoesWhateverItDoes()
//    {
//        $chars = new CharCollection($exp = 'A character set');
//        // $chars->merge([12 => 'p', 15 => ' sandwich']);
//        $chars->merge('foo');
//        $this->assertEquals('A character pet sandwich', (string) $chars);
//    }
}