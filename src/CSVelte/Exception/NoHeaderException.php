<?php namespace CSVelte\Exception;
/**
 * CSVelte\Exception\NoHeaderException
 * There are various methods throughout the library that expect a CSV source to
 * have a header row. Rather than doing something like:

if (if $file->hasHeader()) {
    $header = $file->getHeader()
}

 * you can instead simply call $header->getHeader() and handle this exception if
 * said file has no header
 *
 * @package   CSVelte\Exception
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class NoHeaderException extends CSVelteException
{

}
