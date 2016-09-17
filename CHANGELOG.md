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

## [v0.2 Release](https://github.com/nozavroni/csvelte/milestone/4?closed=1)

 * Fixed issues causing PHP7 build failures ([#75](https://github.com/nozavroni/csvelte/issues/75))
 * Refactored I/O classes, combining the Input and Output namespaces and classes into a single IO\Stream class.  ([#97](https://github.com/nozavroni/csvelte/issues/97), [#99](https://github.com/nozavroni/csvelte/issues/99), [#108](https://github.com/nozavroni/csvelte/issues/108), [#115](https://github.com/nozavroni/csvelte/issues/115) and various others)
 * Created new ``src/autoload.php`` file, rather than requiring autoloader users to include `src/CSVelte/Autoloader.php` ([#106](https://github.com/nozavroni/csvelte/issues/106))
 * Added ``Reader::toArray()`` method to convert a CSV dataset to a two-dimensional array ([#105](https://github.com/nozavroni/csvelte/issues/105))
 * Refactored Readable, Writable, and Seekable interfaces ([#97](https://github.com/nozavroni/csvelte/issues/97) and various others)
 * Reader, writer and anywhere else that accepts a ``Flavor`` object can now also accept an associative array of flavor attributes that will override the default flavor ([#54](https://github.com/nozavroni/csvelte/issues/54))
 * Documentation rewritten almost completely, many sections elaborated and clarified, as well as better organization ([#87](https://github.com/nozavroni/csvelte/issues/87) and [#95](https://github.com/nozavroni/csvelte/issues/95))
 * Various other minor bug fixes and improvements
