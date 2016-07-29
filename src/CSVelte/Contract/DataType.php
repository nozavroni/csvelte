<?php namespace CSVelte\Contract;
/**
 * Data Type interface
 * Implement this interface to be a "data type"
 *
 * @package   CSVelte\Contract
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
interface DataType
{
    public function isValid();
    public function getValue();
    public function __toString();
    //protected function fromString($str);
}
