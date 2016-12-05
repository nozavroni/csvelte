<?php
namespace CSVelteTest\Collection;

use CSVelte\Collection\Collection;
use CSVelte\Collection\MultiCollection;

use function CSVelte\is_traversable;

class MultiCollectionTest extends AbstractCollectionTest
{
    public function testFactoryReturnsMultiCollection()
    {
        $input = [
            'foo' => [
                'test',
                'this',
                'that',
                'boo',
                'zoo',
                'mpo'
            ],
            'test' => [
                'a','b','c','d','e','f','g'
            ],
            'another' => 'this is a string',
            [
                'an',
                'array',
                'of',
                'strings'
            ]
        ];
        $coll = Collection::factory($input);
        $this->assertInstanceOf(MultiCollection::class, $coll);
        $coll2 = Collection::factory($this->testdata[MultiCollection::class]);
        $this->assertInstanceOf(MultiCollection::class, $coll2);
    }

    public function testToArrayReturnsMultiDimensionalArray()
    {
        $coll = Collection::factory($this->testdata[MultiCollection::class]);
        $arr = $coll->toArray();
        $this->assertArrayHasKey('names', $arr);
        $this->assertArrayHasKey('addresses', $arr);
        $this->assertArrayHasKey('cities', $arr);
        $this->assertArrayHasKey('userAgent', $arr);
        $this->assertEquals(3, count($arr['words'][0]));
    }

    public function testMergeMultiCollectionCanMergeArray()
    {
        $coll = Collection::factory($this->testdata[MultiCollection::class]);
        $arr = [
            'names' => [
                'Nobody',
                'Somebody',
                'Thembody'
            ],
            'words' => [
                'I',
                'like',
                'stuff'
            ],
            'cities' => 'This is not a city'
        ];
        $merged = $coll->merge($arr);
        $this->assertEquals(array_merge($coll->toArray(), $arr), $merged->toArray());
    }

    public function testMergeMultiCollectionCanMergeCollection()
    {
        $coll = Collection::factory($this->testdata[MultiCollection::class]);
        $arr = [
            'names' => [
                'Nobody',
                'Somebody',
                'Thembody'
            ],
            'words' => [
                'I',
                'like',
                'stuff'
            ],
            'cities' => 'This is not a city'
        ];
        $mergeme = Collection::factory($arr);
        $merged = $coll->merge($mergeme);
        $this->assertEquals(array_merge($coll->toArray(), $arr), $merged->toArray());
    }

    public function testMultiContainsSearchesThroughoutAllDimensions()
    {
        $coll = Collection::factory($this->testdata[MultiCollection::class]);
        //dd($coll);
        $this->assertTrue($coll->contains('Mrs. Aaliyah Paucek Jr.'));
        $this->assertFalse($coll->contains('Mrs. Aaliyah Paucek Jr.', 8));
        $this->assertTrue($coll->contains('Mrs. Aaliyah Paucek Jr.', 7));
        $this->assertFalse($coll->contains('Mrs. Aaliyah Paucek Jr.', [1,2,3]));
        $this->assertTrue($coll->contains('Mrs. Aaliyah Paucek Jr.', [6,7,8,9]));
        $this->assertTrue($coll->contains('praesentium'));
        $this->assertTrue($coll->contains('praesentium', 1));
        $this->assertFalse($coll->contains('praesentium',2));
        $this->assertFalse($coll->contains('praesentium', [0,2]));
        $this->assertTrue($coll->contains('praesentium', [0,1]));

        $func = function($val, $key) {
            if ($key == 'cities') {
                if (is_traversable($val)) {
                    foreach ($val as $k => $v) {
                        if (strpos($v, 'Waldo') !== false) {
                            return true;
                        }
                    }
                }
            }
            return false;
        };
        $this->assertTrue($coll->contains($func));

        // @todo Come back to this...
        // $this->assertFalse($coll->contains($func), 'names');
    }

}