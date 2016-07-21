<?php namespace CSVelte\Table\DataType;

use Carbon\Carbon;
use Carbon\CarbonInterval;

/**
 * Duration data type
 *
 * @package    CSVelte
 * @subpackage CSVelte\Table\DataType
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 * @see ISO-8601 Durations: https://en.wikipedia.org/wiki/ISO_8601#Durations
 */
class Duration extends AbstractType
{
    // protected $initStr;

    protected $type = 'duration';

    protected function convert($val)
    {
        $intvl = null;
        if (is_object($val)) {
            if ($val instanceof Duration) {
                return $val->getValue();
            } elseif ($val instanceof \DateInterval) {
                // if (self::wasCreatedFromDiff($val)) {
                //     dd($val, false, 'created from diff');
                // }
                $intvl = $val;
            }
        } elseif (is_string($val) && !empty($val)) { // @todo test empty string
            $firstchar = $val[0];
            if ($firstchar == '+' || $firstchar == '-') {
                $val = substr($val, 1);
            }
            // this will throw an exception on invalid duration string
            $intvl = new \DateInterval($val);
            if ($firstchar == '-') $intvl->invert = 1;

            // save original duration string if it passes muster because otherwise
            // \DateInterval will munch it! And it provides no method to recreate
            // it other than writing your own
            // $this->initStr = $val;

            // it would probably be better if I wrote a callable to "normalize"
            // an IntervalSpec rather than simply storing and returning whatever
            // came in through the initialization value, but I don't want to
            // spend any more time than I have to on this data type, as I don't
            // see it being particularly useful and/or necessary, at least not
            // yet. And I could definitely be putting my time to better use
            // working on the Table class and its interaction with Readers and
            // Writers and whatever else... 7/21/2016 at 12:40pm
        }
        if (is_null($intvl)) throw new \InvalidArgumentException('DataType "' . $this->getType() . '" initialized with invalid value: "' . $val . '"');
        try {
            return CarbonInterval::instance($intvl);
        } catch (\InvalidArgumentException $e) {
            // most likely means intvl was created from a diff, which is not allowed
            return new CarbonInterval(
                0, // years
                0, // months
                0, // weeks
                $intvl->format('%a'), // days
                $intvl->format('%h'), // hours
                $intvl->format('%i'), // minutes
                $intvl->format('%s') // seconds
            );
            // $days = $intvl->format('%a');
            // $timeincs = array_map(function($i){
            //     if (preg_match('/^([0-9]+)[HMS]$/', $i, $match)) {
            //         return (bool) $match[1] ? $i : '';
            //     }
            // }, array(
            //     $intvl->format('%hH'),
            //     $intvl->format('%iM'),
            //     $intvl->format('%sS'),
            // ));
            // $ci = new CarbonInterval(
            //     0, // years
            //     0, // months
            //     0, // weeks
            //     $intvl->format('%a'), // days
            //     $intvl->format('%h'), // hours
            //     $intvl->format('%i'), // minutes
            //     $intvl->format('%s') // seconds
            // );
            // $intvl = new CarbonInterval($intvl->format("P%aDT") . implode('', $timeincs));
        }
    }

    // public function __toString()
    // {
    //     return $this->initStr;
    // }

    /**
     * @note This only works for PHP5.6+ but no biggie it's only for debugging
     * purposes anyway... plus it doesn't break anything for earlier versions it
     * simply gets ignored.
     */
    public function __debuginfo()
    {
        return array(
            'type' => $this->type,
            'value' => $this->value
        );
    }
}
