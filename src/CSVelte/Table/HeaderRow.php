<?php
/**
 * CSVelte: Slender, elegant CSV for PHP
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @version   v0.2.1
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace CSVelte\Table;
/**
 * Table Header Row
 * A specialized version of CSVelte\Table\Row that represents a header row.
 *
 * @todo Notes about implementation of headers as row indexes...
 * Because property names adhere to a much stricter naming scheme, any array key
 * that can not be used, directly, as a property name must be converted to a
 * proper property name. Also, because data in this library comes from one of the
 * most notoriously malformed of the data formats (CSV), you have to account for
 * the possibility of things such as duplicate header names. So if the row parser
 * should encounter two identical header names, it should make some attempt to
 * differentiate them before passing them in as array keys.
 *
 * @package   CSVelte\Reader
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @todo This may need its own toArray() method so that it doesnt return an
 *     array with itself as keys 
 */
class HeaderRow extends AbstractRow
{

}
