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

    public function testContainsChecksForTheExistenceOfCharacter()
    {
        $chars = new CharCollection($exp = 'A character set');
        $this->assertTrue($chars->contains('a'));
        $this->assertFalse($chars->contains('b'));
        $this->assertTrue($chars->contains('A'));
        $this->assertFalse($chars->contains('C'));
    }

    public function testContainsChecksForTheExistenceOfCharacterAtGivenPosition()
    {
        $chars = new CharCollection($exp = 'A character set');
        $this->assertTrue($chars->contains('a', 4));
        $this->assertFalse($chars->contains('a', 0));
        $this->assertTrue($chars->contains('A', 0));
        $this->assertFalse($chars->contains('S', 12));
    }

    public function testContainsChecksForTheExistenceOfCharacterViaCallback()
    {
        $chars = new CharCollection($exp = 'A character set');
        $this->assertTrue($chars->contains(function($val) {
            return $val > 's';
        }));
        $this->assertTrue($chars->contains(function($val) {
            return $val > 'S';
        }));
        $this->assertFalse($chars->contains(function($val) {
            return $val > 8;
        }));
        $this->assertFalse($chars->contains(function($char) {
            return is_numeric($char);
        }));
    }

    public function testPopRemovesTheLastChar()
    {
        $me = new CharCollection($exp = 'Luke Visinoni');
        $this->assertEquals('i', $me->pop());
        $this->assertEquals('n', $me->pop());
        $this->assertEquals('Luke Visino', (string) $me);
    }

    public function testShiftRemovesTheFirstChar()
    {
        $me = new CharCollection($exp = 'Luke Visinoni');
        $this->assertEquals('L', $me->shift());
        $this->assertEquals('u', $me->shift());
        $this->assertEquals('ke Visinoni', (string) $me);
    }

    public function testPushAddsCharToEnd()
    {
        $me = new CharCollection($exp = 'Luke Visinoni');
        $notme = $me->push('i');
        $this->assertEquals('Luke Visinonii', (string) $notme);
    }

    public function testUnshiftAddsCharToBeginning()
    {
        $me = new CharCollection($exp = 'Luke Visinoni');
        $notme = $me->unshift('i');
        $this->assertEquals('iLuke Visinoni', (string) $notme);
    }

    public function testPadRepeatsCharacterXTimes()
    {
        $str = new CharCollection('-');
        $str = $str->pad(10, '-');
        $this->assertEquals('----------', (string) $str);
    }

    /**
     * @todo When map callback returns str w/more than a single char
     *       it causes problems. Come back to that.
     */
    public function testMapUsesCallbackOnEachChar()
    {
        $str = new CharCollection($exp = 'abcdefghijklmnopqrstuvwxyz');
        $newstr = $str->map(function($char){
            return ord($char) < 110 ? '-' : $char;
        });
        $this->assertEquals('-------------nopqrstuvwxyz', (string) $newstr);
    }

    public function testWalkUsesCallbackOnEachChar()
    {
        $str = new CharCollection($exp = 'abcdefghijklmnopqrstuvwxyz');
        $exp = [];
        $str->walk(function($val, $key, $extra) use (&$exp) {
            if ($key % 2 == 0) {
                $exp[] = $val . $extra[0];
            } else {
                $exp[] = $val . $extra[1];
            }
        }, ['foo','bar']);
        $this->assertEquals("afoo", $exp[0]);
        $this->assertEquals("bbar", $exp[1]);
    }

    public function testReduceUsesCallbackToReturnSingleValue()
    {
        $str = new CharCollection($exp = 'abcdefghijklmnopqrstuvwxyz');
        $isstr = $str->reduce(function($carry, $elem){
            return (is_string($elem) && $carry);
        }, true);
        $this->assertTrue($isstr);
        $isnum= $str->reduce(function($carry, $elem){
            return (is_numeric($elem) && $carry);
        }, true);
        $this->assertFalse($isnum);
    }

    public function testFilterRemovesCorrectChars()
    {
        $str = new CharCollection($exp = 'abcdefghijklmnopqrstuvwxyz');
        $newstr = $str->filter(function($val) {
            return ($val > 'm');
        });
        $this->assertEquals('nopqrstuvwxyz', (string) $newstr);
    }

    public function testFirstReturnsFirstMatchingValue()
    {
        $str = new CharCollection($exp = 'I like char collections.');
        $char = $str->first(function($val) {
            return ($val > 'm');
        });
        $this->assertEquals('r', $char);
    }

    public function testLastReturnsLastMatchingValue()
    {
        $str = new CharCollection($exp = 'I like char collections.');
        $char = $str->last(function($val) {
            return ($val > 'm');
        });
        $this->assertEquals('s', $char);
    }

    public function testReverse()
    {
        $str = new CharCollection($exp = 'I like char collections.');
        $this->assertEquals(strrev($exp), (string) $str->reverse());
    }

    public function testUnique()
    {
        $str = new CharCollection($exp = 'I like char collections.');
        $this->assertEquals('I likecharotns.', (string) $str->unique());
    }
}