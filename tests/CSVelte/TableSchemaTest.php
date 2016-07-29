<?php

use PHPUnit\Framework\TestCase;
use CSVelte\Table\Schema;
use CSVelte\Table\Schema\ColumnSchema;
use CSVelte\Table\Data\StringValue;

/**
 * CSVelte\Table\Schema Tests
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class TableSchemaTest extends TestCase
{
    public function testMostBasicSchema()
    {
        $columns = array('id' => array('type' => 'integer'));
        $schema = new Schema($columns);
        $idCol = $schema->getColumnSchema('id');
        $this->assertEquals('integer', $idCol->getType());
    }

    public function testColumnTypeDefaultsToString()
    {
        $columns = array('id' => array());
        $schema = new Schema($columns);
        $idCol = $schema->getColumnSchema('id');
        $this->assertEquals('string', $idCol->getType());
    }

    public function testPropertyMagicGetters()
    {
        $columns = array('id' => array('name' => 'Identifier', 'type' => 'integer'));
        $schema = new Schema($columns);
        $idCol = $schema->getColumnSchema('id');
        $this->assertEquals('integer', $idCol->getType());
        $this->assertEquals('Identifier', $idCol->getName());
        $this->assertEquals(null, $idCol->getDescription());
    }

    // public function testColumnSchemaWithConstraints()
    // {
    //     $column = new ColumnSchema('email', array(
    //         'type' => 'string',
    //         'format' => 'email',
    //         'constraints' => array(
    //             'required' => true,
    //             'maxLength' => 255,
    //             'unique' => true,
    //             'pattern' => '/^[A-Z0-9._%+-]{1,64}@(?:[A-Z0-9-]{1,63}\.){1,125}[A-Z]{2,63}$/i'
    //         )
    //     ));
    //     $this->assertTrue($column->getConstraint('required'));
    //     $this->assertTrue($column->getConstraint('unique'));
    //     $this->assertEquals(255, $idCol->getConstraint('maxLength'));
    //     $this->assertEquals('/^[A-Z0-9._%+-]{1,64}@(?:[A-Z0-9-]{1,63}\.){1,125}[A-Z]{2,63}$/i', $idCol->getConstraint('pattern'));
    // }

    // public function testColumnSchemaCanValidateAgainstDataAgainstConstraints()
    // {
    //     $column = new ColumnSchema('email', array(
    //         'type' => 'string',
    //         'format' => 'email',
    //         'constraints' => array(
    //             'required' => true,
    //             'maxLength' => 255,
    //             'unique' => true,
    //             'pattern' => '/^[A-Z0-9._%+-]{1,64}@(?:[A-Z0-9-]{1,63}\.){1,125}[A-Z]{2,63}$/i'
    //         )
    //     ));
    //     $goodvalue = new StringValue('luke.visinoni@gmail.com');
    //     $missingvalue = new StringValue('');
    //     $toolongvalue = new StringValue('lukeyisthecoolestguy.visinoniisthecoolestnameinthewholeworld@somereally.longwebsite.thatisway.toolong.gmail.somereally.longwebsite.thatisway.toolong.gmailisareally.longemailaddress.longwebsite.thatisway.toolong.gmailisareally.longemailaddress.thisshoulddoit.com');
    //     $badvalue = new StringValue('!luke.visinoni@gmail.com');
    //     $this->assertFalse($column->isValid($goodvalue));
    // }

    // public function test

}
