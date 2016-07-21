<?php namespace CSVelte\Exception;
/**
 * CSVelte\Exception\DataTypeInferenceException
 * Thrown when attempting to infer a piece of data's "DataType" and being unable
 * to do so. This exception is thrown so that it may, hopefully bubble up to
 * somewhere that can then just use a default DataType or something.
 *
 * @package   CSVelte\Exception
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class DataTypeInferenceException extends CSVelteException
{

}
