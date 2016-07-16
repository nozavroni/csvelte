<?php namespace CSVelte\Flavor;
/**
* Excel CSV "flavor"
* This is the most common flavor of CSV as it is what is produced by Excel, the
* 900 pound Gorilla of CSV importing/exporting. It is also technically the
* "standard" CSV format according to RFC 4180
*
* @package   CSVelte\Reader
* @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
* @author    Luke Visinoni <luke.visinoni@gmail.com>
* @see       https://tools.ietf.org/html/rfc4180
 */
class ExcelTab extends Excel
{
    protected $delimiter = "\t";
}
