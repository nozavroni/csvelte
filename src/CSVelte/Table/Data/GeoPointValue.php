<?php namespace CSVelte\Table\Data;

/**
 * GeoPoint Value Class
 *
 * @package    CSVelte
 * @subpackage CSVelte\Table\Data
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 */
class GeoPointValue extends Value
{
    // protected $pattern = '/()/';

    const LATITUDE = 0;
    const LONGITUDE = 1;

    protected $pattern = '/^(-?[0-9]{1,3}(\.[0-9]+)?), ?(-?[0-9]{1,3}(\.[0-9]+)?)$/';

    /**
     * @var array
     */
    protected $value;

    protected function fromString($str)
    {
        if (!is_string($str)) {
            if (is_array($str)) {
                if (count($str) != 2) {
                    // invalid, throw exception?
                }
                $str = implode(",", $str);
            } else {
                // invalid throw exception?
                // @todo Actually I think I'm supposed to support like a generic
                // object like {'lon': 10, 'lat': 50}
            }
        }
        if (strpos($str, ",") === false) {
            // invalid coords throw exception?
            throw \InvalidArgumentException('Invalid coordinates supplied to ' . __CLASS__ . ': ' . $str);
        }
        $coords = explode(",", $str, 2);
        return $coords;
    }
}
