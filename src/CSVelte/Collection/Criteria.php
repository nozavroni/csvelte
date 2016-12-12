<?php
/**
 * CSVelte: Slender, elegant CSV for PHP
 *
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @version   v${CSVELTE_DEV_VERSION}
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */

namespace CSVelte\Collection;

class Criteria
{
    /** Use this operator constant to test for identity (exact same) **/
    const ID = '===';

    /** Use this operator constant to test for non-identity **/
    const NID = '!==';

    /** Use this operator constant to test for equality **/
    const EQ = '==';

    /** Use this operator constant to test for non-equality **/
    const NEQ = '!=';

    /** Use this operator constant to test for less-than **/
    const LT = '<';

    /** Use this operator constant to test for greater-than or equal-to **/
    const LTE = '<=';

    /** Use this operator constant to test for greater-than **/
    const GT = '>';

    /** Use this operator constant to test for greater-than or equal-to **/
    const GTE = '>=';

    /** Use this operator constant to test for case insensitive equality **/
    const LIKE = 'like';

    /** Use this operator constant to test for case instensitiv inequality **/
    const NLIKE = '!like';

    /** Use this operator constant to test for internal PHP types **/
    const TOF = 'typeof';

    /** Use this operator constant to test for internal PHP type (negated) **/
    const NTOF = '!typeof';

    /** Use this operator constant to test against a regex pattern **/
    const MATCH = 'match';

    /** Use this operator constant to test against a regex pattern (negated) **/
    const NMATCH = '!match';
}