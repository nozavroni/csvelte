![screen shot 2016-07-13 at 10 18 42 am](https://cloud.githubusercontent.com/assets/17840996/16812744/5b92f468-48e3-11e6-9d2a-5c735b1596c6.png)

# Introducing CSVelte
### A modern solution to an age-old problem

It's been over half a decade since I [released PHP CSV Utilities v0.3](http://www.devnetwork.net/viewtopic.php?f=50&t=115633). Since then, PHP has changed drastically (not to mention the changes I've gone through... hello gray hairs!). It's grown up quite a bit. We've gotten namespaces, traits, anonymous functions, generators, and a bunch more cool features since I last wrote a line of code related to CSV and tabular data. And seeing as PHP has given me so much cool new stuff to work with, I decided the library should get a shiny new name. So, without further ado, please welcome the new and improved and updated for PHP5.3+, CSVelte! Pronounced just like the word "svelte". You see what I did there? Pretty clever, ain't I?

## Getting Started

CSVelte requires at least PHP5.3

### Installation

#### With Composer

To install using composer, just use the following command. That's it. Happy coding.

```bash
$ composer install lukev/csvelte
```

#### Without Composer (direct download)

You should be using [Composer](https://getcomposer.org/). Did you not see how easy that was? It's stupid easy. Stupid easy. Use Composer. Anyway, if you insist, you may download the latest version of CSVelte using the button above, and then include the autoloader file, which will register CSVelte's autoload function for you. That's it. Happy coding.

```php
<?php
// just include this line and classes will be auto-loaded for you
require_once "/path/to/CSVelte/src/AutoLoader.php";

$reader = CSVelte::reader("./files/input.csv");
```

## Reading CSV data

Reading CSV data from a local file is as simple as creating a reader object and iterating over it using foreach. As long as the data even slightly resembles CSV data, the reader should be able to figure out how to read it. You don't need to tell it what line endings are being used, what the delimiter character is, what the quote character is, etc. It will usually be able to figure all that out on its own. You just tell it where to find a CSV file and you're golden.

```php
<?php
foreach ($reader = CSVelte::reader('./data/products.csv') as $line_no => $row) {
    // $row will now be a CSVelte\Table\Row object (which is an iterator)
    foreach ($row as $label => $data) {
        // $label will now be the column header value (if there is one, otherwise it will be a numeric index)
        // $data will now be a CSVelte\Table\Cell object
        $str = (string) $data; // can be converted to string this easily
        $val = $data->getValue(); // or use this to get semantic value
        do_something_useful_with($label, $str);
    }
}
```

It is however, possible to explicitly tell the reader class what "flavor" of CSV you are working with via the CSVelte\Flavor class. To do this, you simply create a new flavor class and pass it as the second argument to the reader method. If you pass a flavor object to the reader, it turns autodetection off completely. So don't expect that whatever attributes you don't pass to your flavor object will be ascertained by the reader. They won't. It will just use some sane default instead.

```php
<?php
$flvr = new Flavor(array(
    'delimiter' => "\t",
    'lineTerminator' => "\n",
    'quoteStyle' => Flavor::QUOTE_ALL,
    'header' => false
));
$reader = CSVelte::reader('./data/products.csv', $flvr);
```

### Reading the header row

CSV files can contain a header row as the first row in the file, but CSV as a format doesn't have a native mechanism for specifying metadata such as whether or not a header row is included. CSVelte's autodetect mechanism will do its best to determine whether or not there is a header row, but if you like, you can specify this using the flavor object by setting the "header" attribute to "true". If the flavor's header attribute is set to true, the header row will be skipped when iterating with foreach and rows can then be referenced by their column header. For example:

```php
<?php
$flavor = new CSVelte\Flavor(array('header' => true));
$reader = CSVelte::reader('./data/products.csv', $flavor);
foreach ($reader as $line_no => $row) {
    // now you can do this...
    $product = $row['name'];
    $price = $row['price'];
}
```

## Writing CSV data

Writing CSV data to a local file is made super easy by CSVelte as well. In fact, the process looks pretty similar to reading CSV data. To write a CSV file, simply pass the name of the file you want to write to CSVelte::writer() and it will return a CSVelte\Writer object for that file (the file will be created if it doesn't exist). You can then use this object to write as many rows of CSV data as you like. By default, CSVelte\Writer will output data in the flavor of CSV specified by <a href="https://tools.ietf.org/html/rfc4180">RFC 4180</a> (Excel's version of CSV).

```php
<?php
$writer = CSVelte::writer('./reports/2016-04-23.csv');
$writer->writeRow(array('colbert', 'for', 'prez', '2020'));
```

Anything iterable can be passed to writeRow or writeRows. And writeRows just calls writeRow repeadedly, meaning that if its an iterable of iterables, you can pass it to writeRows. Just make sure that each iteration has the same number of elements.

### Writing other flavors of CSV

You can tell the writer object to write out different flavors of CSV (using tabs instead of commas or backslash instead of two double quotes, etc.) by passing it a CSVelte\Flavor object, configured to your particular flavor of CSV.

```php
<?php
// you can use one of the preconfigured flavors...
$flavor = new CSVelte\Flavor\ExcelTab;
// or configure your own
$flavor = new CSVelte\Flavor(array(
    'lineTerminator' => "\n",
    'quoteStyle' => Flavor::QUOTE_NONNUMERIC
));
// or a little of both
$flavor = new CSVelte\Flavor\ExcelTab(array(
    'lineTerminator' => "\n"
));
$writer = CSVelte::writer('./files/products.csv', $flavor);
```

### Writing the header row

So what about the header row? Do you just always make sure to pass the header row as the first call to writeRow()? Or is there some explicit way to specify the header row? Well, you _can_ pass your header row as the first row to writeRow(). That would certainly work. So long as you were certain no other rows had been written to the output file already. To more explicitly write your header row, make sure the flavor object passed to your writer has the "header" attribute set to "true". Then call setHeaderRow() on your writer, passing it an array containing your header values.

```php
<?php
$flavor = new CSVelte\Flavor(array('header' => true));
$writer = CSVelte::writer('./files/products.csv', $flavor);
$writer->setHeaderRow(array('id','sku','name','description','price'));
$writer->writeRow(array(1,'PNUL1937u','Some Product', 'A product that does stuff', '$19.99'));
// ...etc
```

*Note:* Just be careful not to call setHeaderRow _after_ any data has been written to the output file, as it will result in an exception. Also, if you're calling setHeaderRow() and the resulting file doesn't contain your header row, make sure the flavor object has its "header" attribute set to "true".

## Learn More

### Official Documentation

Head on over to the [documentation site](http://csvelte.phpcsv.com/) for instructions on reading and writing CSV files, auto-detecting CSV format, and more. You can also peruse the [API documentation](http://csvelte.phpcsv.com/apidocs/index.html) to discover all kinds of neato features I don't have time to fully document.

### Mailing List

For questions and in-depth discussion of CSVelte and CSV on the web, join the [mailing list](https://groups.google.com/forum/#!forum/csvelte-users).

## Contribute

CSVelte is a free (as in beer) library. I work on it in my spare time. If you like it, feel free to buy me a beer (you can PayPal me at luke.visinoni@gmail.com). Contributions will help me to keep pumping out new features and bug fixes and to, eventually, create a dedicated CSVelte website. Or, if you have an idea for a new feature or a bug report, feel free to [submit a pull request](https://github.com/deni-zen/csvelte/pulls).

## License

CSVelte is also free as in speach. It is licensed under the MIT license, meaning basically, you can do whatever you want with it so long as you include the original copyright and license notice in any copy of the software/source.

## About the author

CSVelte was designed and developed by [Luke Visinoni](https://github.com/deni-zen). Feel free to [drop me a line](mailto:luke.visinoni@gmail.com) if you want to tell me how great it is or even if you want to tell me it's an abomination and that I should be shot. I'd love to hear from you either way!

## Credits and Special Thanks

I would just like to thank Pádraic Brady and anybody else working on the Mockery library for their extremely clean and elegant Github/Travis-CI/Scrutinizer/Coveralls/etc. integration code. Without it, this library would have taken much longer to see its first release. And although I tried to simply take inspiration from it, I found it hard not to simply copy what they did. But as they say, [imitation is the sincerest form of flattery](https://books.google.com/books?id=6AclAAAAMAAJ&pg=PA114#v=onepage&q&f=false).

I would also like to thank the small, tight-knit community of developers at the <a href="http://devnetwork.net/">PHP Developer's Network Forums</a>. I honestly don't know where I'd be without those guys. They taught me everything I know!

Finally, I'd like to thank both the members of the [CSV on the Web Working Group](https://www.w3.org/2013/csvw/wiki/Main_Page) as well as the authors of [Python PEP #305](https://www.python.org/dev/peps/pep-0305/), which was actually the inspiration for the first permutation of this library (PHP CSV Utilities) almost a decade ago.
