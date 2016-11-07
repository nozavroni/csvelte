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

use \Iterator;
use \ArrayIterator;
use CSVelte\Collection\AbstractCollection;
use CSVelte\Collection\Collection;
use CSVelte\Contract\Collectable;
use CSVelteTest\UnitTestCase;

class CollectionTest extends UnitTestCase
{
//    public function testCollectFactoryReturnsCollectable()
//    {
//        $coll = Collection::factory();
//        $this->assertInstanceOf(Collectable::class, $coll);
//    }

    public function testCollectFactoryReturnsBasicCollectionByDefault()
    {
        $coll = Collection::factory();
        $this->assertInstanceOf(Collection::class, $coll);
    }

    public function testCollectionFactoryPassesInputToCollection()
    {
        $in = ['foo' => 'bar', 'baz' => 'bin'];
        $coll = Collection::factory($in);
        $this->assertEquals($in, $coll->toArray());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCollectionThrowsExceptionIfPassedInvalidData()
    {
        $in = false;
        Collection::factory($in);
    }

    public function testCollectionAcceptsArrayOrIterator()
    {
        $arr = ['foo' => 'bar', 'baz' => 'bin'];
        $arrColl = Collection::factory($arr);
        $this->assertEquals($arr, $arrColl->toArray());

        $iter = new ArrayIterator($arr);
        $iterColl = Collection::factory($iter);
        $this->assertEquals(iterator_to_array($iter), $iterColl->toArray());
    }

    public function testCollectionGetReturnsValueAtIndex()
    {
        $in = ['foo' => 'bar', 'baz' => 'bin'];
        $coll = Collection::factory($in);
        $this->assertEquals('bar', $coll->get('foo'));
    }

    public function testCollectionGetReturnsDefaultIfIndexNotFound()
    {
        $in = ['foo' => 'bar', 'baz' => 'bin'];
        $coll = Collection::factory($in);
        $this->assertEquals('woo!', $coll->get('poo', 'woo!'));
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testCollectionGetThrowsExceptionIfIndexNotFoundAndThrowIsTrue()
    {
        $in = ['foo' => 'bar', 'baz' => 'bin'];
        $coll = Collection::factory($in);
        $coll->get('poo', null, true);
    }

    public function testCollectionSetValue()
    {
        $in = ['foo' => 'bar', 'baz' => 'bin'];
        $coll = Collection::factory($in);
        $this->assertNull($coll->get('poo'));
        $this->assertInstanceOf(AbstractCollection::class, $coll->set('poo', 'woo!'));
        $this->assertEquals('woo!', $coll->get('poo'));
    }

    public function testCollectionDeleteValue()
    {
        $in = ['foo' => 'bar', 'baz' => 'bin'];
        $coll = Collection::factory($in);
        $this->assertNotNull($coll->get('foo'));
        $this->assertInstanceOf(AbstractCollection::class, $coll->delete('foo'));
        $this->assertNull($coll->get('foo'));
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testCollectionDeleteValueThrowsExceptionIfThrowIsTrue ()
    {
        $in = ['foo' => 'bar', 'baz' => 'bin'];
        $coll = Collection::factory($in);
        $coll->delete('boo', true);
    }

    public function testCollectionToArrayCallsToArrayRecursively()
    {
        $in1 = ['foo' => 'bar', 'baz' => 'bin'];
        $in2 = ['boo' => 'far', 'biz' => 'ban'];
        $in3 = ['doo' => 'dar', 'diz' => 'din'];
        $coll1 = Collection::factory($in1);
        $coll2 = Collection::factory($in2);
        $coll2->set('coll1', $coll1);
        $coll3 = Collection::factory($in3);
        $coll3->set('coll2', $coll2);
        $this->assertEquals([
            'doo' => 'dar', 'diz' => 'din',
            'coll2' => [
                'boo' => 'far', 'biz' => 'ban',
                'coll1' => [
                    'foo' => 'bar', 'baz' => 'bin'
                ]
            ]
        ], $coll3->toArray());
    }

    public function testCollectionKeysReturnsCollectionOfKeys()
    {
        $in = ['foo' => 'bar', 'baz' => 'bin'];
        $coll = Collection::factory($in);
        $this->assertEquals(['foo','baz'], $coll->keys()->toArray());
    }

    public function testCollectionValuesReturnsCollectionOfValues()
    {
        $in = ['foo' => 'bar', 'baz' => 'bin'];
        $coll = Collection::factory($in);
        $this->assertEquals(['bar','bin'], $coll->values()->toArray());
    }

    public function testCollectionMergeMergesDataIntoCollection()
    {
        $in = ['foo' => 'bar', 'baz' => 'bin'];
        $coll = Collection::factory($in);
        $mergeIn = ['baz' => 'bone', 'boo' => 'hoo'];
        $this->assertEquals([
            'foo' => 'bar',
            'baz' => 'bone',
            'boo' => 'hoo'
        ], $coll->merge($mergeIn)->toArray());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCollectionMergeThrowsExceptionOnInvalidDataType ()
    {
        $in = ['foo' => 'bar', 'baz' => 'bin'];
        $coll = Collection::factory($in);
        $coll->merge('boo');
    }

    public function testCollectionContainsReturnsTrueIfRequestedValueInCollection()
    {
        $coll = Collection::factory([
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
        $this->assertTrue($coll->contains(function($val) {
            return strlen($val) > 3;
        }));
        $this->assertFalse($coll->contains(function($val) {
            return strlen($val) < 3;
        }));
        $this->assertFalse($coll->contains(function($val) {
            return $val instanceof Iterator;
        }));
    }
}