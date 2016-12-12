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

use CSVelte\Collection\Collection;
use CSVelte\Collection\MultiCollection;
use CSVelte\Collection\TabularCollection;
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
        $this->testdata[TabularCollection::class] = [
            'user' => [],
            'profile' => []
        ];
        for($t = 1; $t <= 5; $t++) {
            $created = $faker->dateTimeThisYear->format('YmdHis');
            $profile_id = $t + 125;
            $this->testdata[TabularCollection::class]['user'][] = [
                'id' => $t,
                'profile_id' => $profile_id,
                'email' => $faker->email,
                'password' => sha1($faker->asciify('**********')),
                'role' => $faker->randomElement(['user','admin','user','user','user','user','user','moderator','moderator']),
                'is_active' => $faker->boolean,
                'created' => $created,
                'modified' => $created
            ];
            $this->testdata[TabularCollection::class]['profile'][] = [
                'id' => $profile_id,
                'address' => $faker->streetAddress,
                'city' => $faker->city,
                'state' => $faker->stateAbbr,
                'zipcode' => $faker->postcode,
                'phone' => $faker->phoneNumber,
                'bio' => $faker->paragraph,
                'created' => $created,
                'modified' => $created
            ];
        }
        $this->testdata[Collection::class] = $faker->words(15);
    }
}