<?php
/**
 * CSVelte: Slender, elegant CSV for PHP
 *
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @copyright Copyright (c) 2018 Luke Visinoni
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   See LICENSE file (MIT license)
 */
namespace CSVelte\Exception;

/**
 * NotYetImplementedException
 *
 * @todo This is thrown when user attempts to use a feature that is not yet implemented. Once I release v1.0, I intend
 *       to remove this exception entirely, but until then it helps me keep certain interfaces consistent.
 */
class NotYetImplementedException extends CSVelteException {}
