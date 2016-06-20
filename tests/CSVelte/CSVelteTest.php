<?php

use PHPUnit\Framework\TestCase;
use CSVelte\CSVelte;

class CSVelteTest extends TestCase
{
    public function testCSVelte()
    {
        $this->assertInstanceOf('CSVelte\CSVelte', new CSVelte);
    }
}
?>
