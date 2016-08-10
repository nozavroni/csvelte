<?php
/**
 * CSVelte: Slender, elegant CSV for PHP
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV 
 * standardization efforts, CSVelte was written in an effort to take all the 
 * suck out of working with CSV. 
 *
 * @version   v0.1
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace CSVelte\Table\Data;

use CSVelte\Utils;

/**
 * Number Value Class
 *
 * @package    CSVelte
 * @subpackage CSVelte\Table\Data
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 */
class NumberValue extends Value
{
    /**
     * @var char
     */
    protected $decimalChar = '.';

    /**
     * @var char
     */
    protected $groupChar = ',';

    /**
     * @var string
     */
    protected $currencyChars = '$£¥';

    /**
     * @var string
     */
    protected $pattern;

    public function __construct($value, $decimalChar = null, $groupChar = null)
    {
        if (!is_null($decimalChar)) {
            $this->setDecimalChar($decimalChar);
        }
        if (!is_null($groupChar)) {
            $this->setGroupChar($groupChar);
        }
        $this->pattern = "/^(\+|-)?[" . preg_quote($this->currencyChars) . "]?[0-9][0-9" . preg_quote($this->groupChar) . "]*(" . preg_quote($this->decimalChar) . "[0-9]+)?([eE](\+|-)?[0-9]+|[‰%])?$/";
        parent::__construct($value);
    }

    protected function setDecimalChar($char)
    {
        if (strlen($char) > 1) {
            // @todo test this
            throw new \InvalidArgumentException('Decimal character must be one character only: ' . $char);
        }
        $this->decimalChar = $char;
    }

    protected function setGroupChar($char)
    {
        if (strlen($char) > 1) {
            // @todo test this
            throw new \InvalidArgumentException('Group character must be one character only: ' . $char);
        }
        $this->groupChar = $char;
    }

    protected function fromString($str)
    {
        $cur = $this->currencyChars;
        $grp = $this->groupChar;
        $stripped = Utils::string_map($str, function($char) use ($cur, $grp) {
            $char = (strpos($cur, $char) === false) ? $char : "";
            return ($char === $grp) ? "" : $char;
        });
        if (strpos($stripped, $this->decimalChar) !== false) {
            $decimal = str_replace($this->decimalChar, '.', $stripped);
            return (float) $decimal;
        }
        return (int) $stripped;
    }
}
