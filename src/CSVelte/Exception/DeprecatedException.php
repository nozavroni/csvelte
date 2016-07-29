<?php namespace CSVelte\Exception;
/**
 * CSVelte\Exception\DeprecatedException
 * This exception is thrown when users attempt to use features of the library
 * that have been deprecated. This allows code to not necessarily braek even when
 * the feature they're trying to use no longer exists or will be removed in the
 * next version.
 *
 * @package   CSVelte\Exception
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class DeprecatedException extends CSVelteException
{

}
