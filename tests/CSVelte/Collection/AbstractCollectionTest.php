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

use CSVelte\Collection\MultiCollection;
use CSVelteTest\UnitTestCase;
use Faker;

class AbstractCollectionTest extends UnitTestCase
{
    protected $testdata = [];

    public function setUp()
    {
        parent::setUp();
        $faker = Faker\Factory::create();
        $faker->seed(19860423);
        $this->testdata[MultiCollection::class] = [
            'names' => [],
            'addresses' => [],
            'cities' => [],
            'dates' => [],
            'numeric' => [],
            'words' => [],
            'userAgent' => []
        ];
        for ($i = 0; $i < 10; $i++) {
            $this->testdata[MultiCollection::class]['names'][] = $faker->name;
            $this->testdata[MultiCollection::class]['addresses'][] = $faker->streetAddress;
            $this->testdata[MultiCollection::class]['cities'][] = $faker->city;
            $this->testdata[MultiCollection::class]['dates'][] = $faker->date;
            $this->testdata[MultiCollection::class]['numeric'][] = $faker->randomNumber;
            $this->testdata[MultiCollection::class]['words'][] = $faker->words;
            $this->testdata[MultiCollection::class]['userAgent'][] = $faker->userAgent;
        }
    }
}