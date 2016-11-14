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
}