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

use ArrayAccess;
use Countable;
use CSVelte\Collection\CharCollection;
use CSVelte\Collection\MultiCollection;
use CSVelte\Collection\NumericCollection;
use CSVelte\Collection\TabularCollection;
use \Iterator;
use \ArrayIterator;
use CSVelte\Collection\AbstractCollection;
use CSVelte\Collection\Collection;
use CSVelte\Contract\Collectable;
use CSVelteTest\UnitTestCase;
use function CSVelte\is_traversable;

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

    public function testCollectionFactoryReturnsTabularCollectionForTabularDataset()
    {
        $in = [
            [
                'foo' => 'far',
                'woo' => 'war',
                'too' => 'tar',
                'coo' => 'car',
                'roo' => 'rar'
            ],
            [
                'foo' => 'yay',
                'woo' => 'bay',
                'too' => 'day',
                'coo' => 'fay',
                'roo' => 'stay'
            ],
            [
                'foo' => 'poo',
                'woo' => 'doo',
                'too' => 'soo',
                'coo' => 'woo',
                'roo' => 'coo'
            ],
            [
                'foo' => '---',
                'woo' => '...',
                'too' => '===',
                'coo' => '~~~',
                'roo' => ',,,'
            ]
        ];
        $table = Collection::factory($in);
        $this->assertInstanceOf(TabularCollection::class, $table);
    }

    public function testCollectionFactoryReturnsMultiCollectionForMultiDimensionalDataset()
    {
        $in = [
            [
                'foo' => 'far',
                'woo' => 'war',
                'too' => 'tar',
                'coo' => 'car',
                'roo' => 'rar'
            ],
            [
                'foo' => 'yay',
                'woo' => 'bay',
                'too' => 'day',
                'coo' => 'fay',
                'roo' => 'stay'
            ],
            [
                'foo' => 'poo',
                'woo' => 'doo',
                'too' => 'soo',
                'coo' => 'woo',
            ],
            [
                'foo' => '---',
                'woo' => '...',
                'too' => '===',
                'coo' => '~~~',
                'roo' => ',,,'
            ]
        ];
        $multi = Collection::factory($in);
        $this->assertInstanceOf(MultiCollection::class, $multi);
        $in = [
            [
                'foo' => 'far',
                'woo' => 'war',
                'too' => 'tar',
                'coo' => 'car',
                'roo' => 'rar'
            ],
            [1,2,3,4,5],
            'foobar',
            [
                'foo' => 'yay',
                'woo' => 'bay',
            ],
            [
                'foo' => '---',
                'woo' => '...',
                'too' => '===',
                'coo' => '~~~',
                'roo' => ',,,'
            ]
        ];
        $multi = Collection::factory($in);
        $this->assertInstanceOf(MultiCollection::class, $multi);
        $in = [
            [
                'foo' => 'far',
                'woo' => 'war',
                'too' => 'tar',
                'coo' => 'car',
                'roo' => 'rar'
            ],
            1,2,3,4,5
        ];
        $multi = Collection::factory($in);
        $this->assertInstanceOf(MultiCollection::class, $multi);
    }

    public function testCollectionFactoryReturnsNumericCollectionForNumericDataset()
    {
        $in = [1,2,3,4,5];
        $numeric = Collection::factory($in);
        $this->assertInstanceOf(NumericCollection::class, $numeric);
        $in = [1,2.5,3,4,5];
        $numeric = Collection::factory($in);
        $this->assertInstanceOf(NumericCollection::class, $numeric);
        $in = [0,0,0,0,'0'];
        $numeric = Collection::factory($in);
        $this->assertInstanceOf(NumericCollection::class, $numeric);
        $in = ['0','123',10];
        $numeric = Collection::factory($in);
        $this->assertInstanceOf(NumericCollection::class, $numeric);
        $in = [1,];
        $numeric = Collection::factory($in);
        $this->assertInstanceOf(NumericCollection::class, $numeric);
    }

    public function testCollectionFactoryReturnsCharCollectionForCharacterSet()
    {
        $chars = 'a set of characters';
        $charColl = Collection::factory($chars);
        $this->assertInstanceOf(CharCollection::class, $charColl);
        $chars = '000';
        $charColl = Collection::factory($chars);
        $this->assertInstanceOf(CharCollection::class, $charColl);
        $chars = 0;
        $charColl = Collection::factory($chars);
        $this->assertInstanceOf(CharCollection::class, $charColl);
        $chars = 12345;
        $charColl = Collection::factory($chars);
        $this->assertInstanceOf(CharCollection::class, $charColl);
    }

    public function testCollectionFactoryReturnsCollectionForEverythingElse()
    {
        $chars = ['a set of characters'];
        $charColl = Collection::factory($chars);
        $this->assertInstanceOf(Collection::class, $charColl);
        $chars = ['000', 0, 'zero'];
        $charColl = Collection::factory($chars);
        $this->assertInstanceOf(Collection::class, $charColl);
        $chars = [0,1,2,3,4,5,'six'];
        $charColl = Collection::factory($chars);
        $this->assertInstanceOf(Collection::class, $charColl);
        $chars = [12345, null, true, false];
        $charColl = Collection::factory($chars);
        $this->assertInstanceOf(Collection::class, $charColl);
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

    public function testCollectionHas()
    {
        $arr = ['foo' => 'bar', 'baz' => 'bin'];
        $arrColl = Collection::factory($arr);
        $this->assertTrue($arrColl->has('foo'));
        $this->assertFalse($arrColl->has('poo'));
    }

    public function testCollectionHasWorksOnNumericKeys()
    {
        $arr = ['foo', 'bar', 'baz', 'bin'];
        $arrColl = Collection::factory($arr);
        $this->assertTrue($arrColl->has(0));
        $this->assertFalse($arrColl->has(5));
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

        // can also return true only for given index(es)
        $this->assertTrue($coll->contains(function($val, $key) {
            return strlen($val) > 3;
        }, 'goo'));
        $this->assertFalse($coll->contains(function($val, $key) {
            return strlen($val) > 3;
        }, 'boo'));

        // check that $key can be used for truthiness checking...
        $this->assertTrue($coll->contains(function($val, $key) {
            if (is_string($key)) {
                return strlen($val) > 3;
            }
            return false;
        }, 'goo'));
        $this->assertFalse($coll->contains(function($val, $key) {
            if (is_numeric($key)) {
                return strlen($val) > 3;
            }
            return false;
        }, 'boo'));
    }

    public function testCollectionContainsAcceptsArrayForIndexParam()
    {
        $coll = Collection::factory([
            'foo' => 'bar',
            'boo' => 'far',
            'goo' => 'czar'
        ]);

        // pass an array of possible indexes
        $this->assertTrue($coll->contains('bar', ['foo','boo']));
        $this->assertFalse($coll->contains('bar', ['goo','too']));

        // we also need to make sure this works with callables
        $this->assertTrue($coll->contains(function($val, $key) {
            return strlen($val) > 3;
        }, ['goo','boo']));
        $this->assertFalse($coll->contains(function($val, $key) {
            return strlen($val) > 3;
        }, ['foo','boo']));

        // check that $key can be used for truthiness checking...
        $this->assertFalse($coll->contains(function($val, $key) {
            if (is_string($key)) {
                return strlen($val) > 3;
            }
            return false;
        }, ['foo','boo']));
        $this->assertFalse($coll->contains(function($val, $key) {
            if (is_numeric($key)) {
                return strlen($val) > 3;
            }
            return false;
        }, ['goo','boo']));
    }

    public function testPopReturnsAnItemAndRemovesItFromEnd()
    {
        $coll = Collection::factory(['a','b','c','d',$expected = 'pop goes the weasel']);
        $this->assertEquals($expected, $coll->pop());
        $this->assertEquals(['a','b','c','d'], $coll->toArray());
        $this->assertEquals('d', $coll->pop());
        $this->assertEquals(['a','b','c'], $coll->toArray());
    }

    public function testShiftReturnsAnItemAndRemovesItFromBeginning()
    {
        $coll = Collection::factory([$expected = 'a','b','c','d','pop goes the weasel']);
        $this->assertEquals($expected, $coll->shift());
        $this->assertEquals(['b','c','d','pop goes the weasel'], $coll->toArray());
        $this->assertEquals('b', $coll->shift());
        $this->assertEquals(['c','d','pop goes the weasel'], $coll->toArray());
    }

    public function testPushItemsOntoCollectionAddsToEnd()
    {
        $coll = Collection::factory(['a','b','c','d']);
        $coll->push('e');
        $this->assertEquals(['a','b','c','d','e'], $coll->toArray());
        $this->assertEquals(['a','b','c','d','e','f','g',['h','i','j'], 'k'], $coll->push('f', 'g', ['h', 'i', 'j'], 'k')->toArray());
    }

    public function testUnshiftAddsToBeginningOfCollection()
    {
        $coll = Collection::factory(['a','b','c','d']);
        $coll->unshift('e');
        $this->assertEquals(['e','a','b','c','d'], $coll->toArray());
        $this->assertEquals(['f','g',['h','i','j'],'k','e','a','b','c','d'], $coll->unshift('f', 'g', ['h', 'i', 'j'], 'k')->toArray());
    }

    public function testMapReturnsANewCollectionContainingValuesAfterCallback()
    {
        $coll = Collection::factory([0,1,2,3,4,5,6,7,8,9]);
        $coll2 = $coll->map(function($val){
            return $val + 1;
        });
        $this->assertInstanceOf(AbstractCollection::class, $coll2);
        $this->assertEquals([1,2,3,4,5,6,7,8,9,10], $coll2->toArray());
    }

    public function testCollectionWalkCallbackModifyInPlace()
    {
        $coll = Collection::factory([1,2,3,4,5,6,7,8,9,0]);
        $context = [
            'extra_context' => 'foobar',
            'more_context' => 'boofar'
        ];
        $coll->walk(function (&$value, $key, $udata) {
            if ($key %2 == 0) $value++;
            else $value--;
            $value .= $udata['extra_context'];
        }, $context);
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

    public function testCollectionReduceReturnsSingleValueUsingCallback()
    {
        $coll = Collection::factory([
            'mk'     => 'lady',
            'lorrie' => 'sweet',
            'luke'   => 'really cool guy',
            'terry'  => 'what a fool'
        ]);
        $this->assertEquals('really cool guy', $coll->reduce(function($carry, $item) {
            if (strlen($item) >= strlen($carry)) {
                return $item;
            }
            return $carry;
        }, null));

    }

    public function testCollectionFilterReturnsCollectionFilteredUsingCallback()
    {
        $coll = Collection::factory([
            'mk'     => 'lady',
            'lorrie' => 'sweet',
            'luke'   => 'really cool guy',
            'terry'  => 'what a fool'
        ]);
        $this->assertEquals([
            'mk'     => 'lady',
            'terry'  => 'what a fool'
        ], $coll->filter(function($v, $k) {
            return strpos($v, 'e') === false;
        })->toArray());
    }

    public function testCollectionIsIterable()
    {
        $coll = Collection::factory($exp = [
            'mk'     => 'lady',
            'lorrie' => 'sweet',
            'luke'   => 'really cool guy',
            'terry'  => 'what a fool',
        ]);
        $this->assertInstanceOf(Iterator::class, $coll);
        $this->assertEquals('mk', $coll->key());
        $this->assertEquals('lady', $coll->current());
        $this->assertTrue($coll->valid());
        $this->assertEquals('sweet', $coll->next());
        $this->assertEquals('lorrie', $coll->key());
        $this->assertEquals('sweet', $coll->current());
        $this->assertTrue($coll->valid());
        $this->assertEquals('really cool guy', $coll->next());
        $this->assertEquals('luke', $coll->key());
        $this->assertEquals('really cool guy', $coll->current());
        $this->assertTrue($coll->valid());
        $this->assertEquals('what a fool', $coll->next());
        $this->assertEquals('terry', $coll->key());
        $this->assertEquals('what a fool', $coll->current());
        $this->assertTrue($coll->valid());
        $this->assertNull($coll->next());
        $this->assertFalse($coll->valid());
        $this->assertEquals('lady', $coll->rewind());

        foreach ($coll as $key => $val) {
            $this->assertEquals($exp[$key], $val);
        }
    }

    public function testSPLIteratorFunctionsWorkOnCollection()
    {
        $coll = Collection::factory($exp = [
            'mk'     => 'lady',
            'lorrie' => 'sweet',
            'luke'   => 'really cool guy',
            'terry'  => 'what a fool',
        ]);
        $arr = iterator_to_array($coll);
        $this->assertEquals($exp, $arr);
        $this->assertEquals($arr, $coll->toArray());
    }

    //public function testToArrayUsesIteratorMethods()
    //{
        // @todo Need to stub the collection and change the "current" method to return something different
        // so I can test that foreach always returns the value that current returns
    //}

    public function testCollectionReturnsTrueForIsTraversable()
    {
        $coll = Collection::factory($exp = [
            'mk'     => 'lady',
            'lorrie' => 'sweet',
            'luke'   => 'really cool guy',
            'terry'  => 'what a fool',
        ]);
        $this->assertTrue(is_traversable($coll));
    }

    public function testOffsetMethodsForCollectionArrayAccess()
    {
        $coll = Collection::factory($exp = [
            'mk'     => 'lady',
            'lorrie' => 'sweet',
            'luke'   => 'really cool guy',
            'terry'  => 'what a fool',
        ]);
        $this->assertInstanceOf(ArrayAccess::class, $coll);
        $this->assertTrue($coll->offsetExists('mk'));
        $this->assertFalse($coll->offsetExists('mom'));
        $this->assertEquals('lady', $coll->offsetGet('mk'));
        $this->assertNull($coll->offsetSet('mk', 'wife'));
        $this->assertEquals('wife', $coll->offsetGet('mk'));
        $coll->offsetSet('mom', 'saint');
        $this->assertTrue($coll->offsetExists('mom'));
        $this->assertEquals('saint', $coll->offsetGet('mom'));
        $this->assertNull($coll->offsetUnset('mom'));
        $this->assertFalse($coll->offsetExists('mom'));

        // now we can test that array syntax works (it will)
        $this->assertTrue(isset($coll['mk']));
        $this->assertEquals('wife', $coll['mk']);
        unset($coll['mk']);
        $this->assertFalse(isset($coll['mk']));
        $coll['foo'] = 'var';
        $this->assertTrue(isset($coll['foo']));
        $this->assertEquals('var', $coll['foo']);
    }

    public function testCollectionIsCountable()
    {
        $coll = Collection::factory($exp = [
            'mk'     => 'lady',
            'lorrie' => 'sweet',
            'luke'   => 'really cool guy',
            'terry'  => 'what a fool',
        ]);
        $this->assertInstanceOf(Countable::class, $coll);
        $this->assertEquals(4, $coll->count());
    }
}