# CHANGE LOG

## [v0.2.2 Release]()

 * Refactored CSVelte\Collection class, introducing a variety of specialized collection classes ([#141](https://github.com/nozavroni/csvelte/issues/141)).
 * Began using StyleCI to enforce consistent coding style ([#169](https://github.com/nozavroni/csvelte/issues/169)).
 * Various other minor refactors (Taster, AbstractRow, etc.)

## [v0.2.1 Release](https://github.com/nozavroni/csvelte/milestone/9?closed=1)

 * Added IO\Resource class to represent a PHP stream resource. Now CSVelte will expect/return an IO\Resource object where before it was a native PHP stream resource ([#114](https://github.com/nozavroni/csvelte/issues/114), [#124](https://github.com/nozavroni/csvelte/issues/124) and [#135](https://github.com/nozavroni/csvelte/issues/135)).
 * Implemented "lazy-open" streams by means of the IO\Resource class I mentioned before ([#121](https://github.com/nozavroni/csvelte/issues/121)).
 * Replaced Readable, Writable, and Seekable interfaces with the much simpler Streamable interface ([#107](https://github.com/nozavroni/csvelte/issues/107)).
 * Added BufferStream class. Any time I need a simple buffer object within CSVelte, I will now use this class ([#126](https://github.com/nozavroni/csvelte/issues/126)).
 * Used the aforementioned BufferStream class to write the IteratorStream class, which loops over an iterator and fills up a buffer that it can then read from until it's empty and the process starts again ([#122](https://github.com/nozavroni/csvelte/issues/122)).
 * Added the Collection class, which is simply a wrapper for an array with a multitude of very convenient methods. Many classes now use this class internally rather than an array. This class replaces the old Utils class (which was  only used internally anyway, it was never part of the public API, whereas Collection may eventually be) 
 * Added the following namespaced factory and helper functions under the "CSVelte" namespace.
    * CSVelte\streamize - a very forgiving IO\Stream object factory (accepts strings, iterators, SplFileObjects, IO\Resource objects, and stream resource variables) ([#123](https://github.com/nozavroni/csvelte/issues/123))
    * CSVelte\stream - a less forgiving IO\Stream object factory (only accepts a stream URI)
    * CSVelte\stream_resource - IO\Resource object factory 
    * CSVelte\taste - a convenience function that instantiates a Taster object and then calls its "taste" method
    * CSVelte\taste_has_header - a convenience function that instantiates a Taster object and then calls its "hasHeader" method
    * CSVelte\collect - a Collection object factory 
    * CSVelte\invoke - a convenience function that accepts an anonymous function/closure, invokes it, and immediately returns the result
 * Removed Carbon dependency ([#128](https://github.com/nozavroni/csvelte/issues/128))
 * Added __invoke() magic method to IO\Stream and various other classes. 
 * Refactored the Taster class as much as possible, although there is still much work to be done on it ([#1](https://github.com/nozavroni/csvelte/issues/1)).
 * Removed all remaining filesystem-reliant unit tests. All filesystem tests now substitute the vfsStream class for the actual filesystem now.
 * Fixed bug that was causing PHP7 build to fail ([#149](https://github.com/nozavroni/csvelte/issues/149))
 * Added a phpcs.xml.dist file, although I am not yet actually running the coding style fixer (that will be in the next version hopefully) ([#153](https://github.com/nozavroni/csvelte/issues/153))
 * Fixed a whole slew of docblock inconsistencies, unused class imports, etc.
 * Improved the documentation at http://csvelte.phpcsv.com/

## [v0.2 Release](https://github.com/nozavroni/csvelte/milestone/4?closed=1)

 * Fixed issues causing PHP7 build failures ([#75](https://github.com/nozavroni/csvelte/issues/75))
 * Refactored I/O classes, combining the Input and Output namespaces and classes into a single IO\Stream class.  ([#97](https://github.com/nozavroni/csvelte/issues/97), [#99](https://github.com/nozavroni/csvelte/issues/99), [#108](https://github.com/nozavroni/csvelte/issues/108), [#115](https://github.com/nozavroni/csvelte/issues/115) and various others)
 * Created new ``src/autoload.php`` file, rather than requiring autoloader users to include `src/CSVelte/Autoloader.php` ([#106](https://github.com/nozavroni/csvelte/issues/106))
 * Added ``Reader::toArray()`` method to convert a CSV dataset to a two-dimensional array ([#105](https://github.com/nozavroni/csvelte/issues/105))
 * Refactored Readable, Writable, and Seekable interfaces ([#97](https://github.com/nozavroni/csvelte/issues/97) and various others)
 * Reader, writer and anywhere else that accepts a ``Flavor`` object can now also accept an associative array of flavor attributes that will override the default flavor ([#54](https://github.com/nozavroni/csvelte/issues/54))
 * Documentation rewritten almost completely, many sections elaborated and clarified, as well as better organization ([#87](https://github.com/nozavroni/csvelte/issues/87) and [#95](https://github.com/nozavroni/csvelte/issues/95))
 * Various other minor bug fixes, additional unit tests, and code improvements

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
