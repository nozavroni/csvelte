v0.1 Release

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

v0.2 Release
