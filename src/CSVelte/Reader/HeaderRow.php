<?php namespace CSVelte\Reader;

use CSVelte\Reader;

/**
 * Reader header row class
 * A specialized version of Reader\Row that represents the header row within CSV
 * input source.
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
 */
class HeaderRow extends RowBase
{
    // /**
    //  * @inheritDoc
    //  */
    // public function __construct(array $headers)
    // {
    //     $headers = $this->propertize($headers);
    //     parent::__construct($headers);
    // }
    //
    // /**
    //  * "Propertize" the header names so that they can be called as properties
    //  */
}
