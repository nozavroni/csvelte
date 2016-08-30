# CHANGE LOG

## [v0.1 Release](https://github.com/nozavroni/csvelte/milestone/1?closed=1)

 * Reader Class
     * "Readables" - Implementors of the CSVelte\Contracts\Readable interface.
         * Local File Readable
         * Stream Readable
         * String Readable
 * Writer Class
    * "Writables" - Implementors of the CSVelte\Contracts\Writable interface.
         * Local File Writable
         * Stream Writable
 * Flavor Class - Represent various "flavors" of CSV. Passed to reader/writer
       classes to tell them what format the CSV data is. This means details such
       as delimiter, line terminator, quote character, etc.
 * Taster Class - Automatically detects "flavor" of CSV data by analyzing a
       sample of it and returning a ``CSVelte\Flavor`` class.
 * Autoloader - Automatically loads class files for those who aren't using PHP's
       de facto package manager, Composer.

## [v0.2 Release](https://github.com/nozavroni/csvelte/milestone/4)

I want to include links to each item's Github issue (if possible) next to each
item listed on this log.
