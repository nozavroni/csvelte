<?php

use PHPUnit\Framework\TestCase;
use CSVelte\CSVelte;

class CSVelteTest extends TestCase
{
    public function testCSVelte()
    {
        $this->assertInstanceOf($expected = 'CSVelte\CSVelte', new CSVelte);
    }

    public function testCSVelteImport()
    {
        $csv = new CSVelte();
        $file = $csv->import("./files/sample1.csv");
        $this->assertInstanceOf($expected = 'CSVelte\File', $file);
    }

    // public function testCSVelteImportFileSize()
    // {
    //     $csv = new CSVelte();
    //     $file = $csv->import("./files/sample1.csv");
    //     $this->assertEquals($expected = 10, $file->size());
    // }
}
?>
