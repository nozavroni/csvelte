# ROADMAP

To keep this project on track, I have included this road map file. It will
simply list the features I intend to implement for each version up until the
first production-ready release, v1.0. After that I'll probably create a new
roadmap document. 

## Version 0.1

See CHANGELOG File

## Version 0.2 - Streams

This release will focus on I/O related functionality. I want to get the
``IO\Stream`` class working in as flexible a manner as possible. I'm also
interested in implementing stream decorators so that it's possible to "decorate"
the base stream class, adding various features and functionality without using
a inheritance. A good example of what I mean is the Guzzle streams library.

### Version 0.2.0

 * Mostly focused on cleanup tasks
 * Completely overhauled unit tests, using vfsStream library rather than making
   actual changes to the file system.
 * Combined all I/O classes down into one ``IO\Stream`` class
 * See changelog for other changes

## Version 0.3 - Tabular data

The focus of version 0.3 will be the ``Table`` namespace. This includes
``Table\Dataset``, which will represent a set of tabular data within CSVelte. I
will also be refactoring the ``Reader`` and ``Writer`` classes, removing all
references to ``Table\AbstractRow`` and its descendants. This will be the
version that takes CSVelte from a CSV library to a tabular data library. It will
pave the way for me to begin implementing things such as table schemas, CSV
validation, row and column manipulation, import and export to various other
formats (including CSV), and much more!

See: https://www.w3.org/TR/2015/REC-tabular-data-model-20151217/#model

## Version 0.4 - Character encoding

This release will focus on all character encoding related functionality,
including transcoding into and out of various character encodings including
UTF8, UTF16, and whatever other encodings are relevant.

## Version 0.5 - Import / Export

This release will focus on import/export to various other file formats. This
includes CSV, XML, JSON, Excel, Google Spreadsheets, and various other
variations of these.

## Version 0.6 - Schemas and other metadata

This release will focus on table schemas and meta data. These will allow me to
describe a table or a set of tables in all the ways in which the CSV on the Web
Working Group describes.

See: https://www.w3.org/TR/2015/REC-tabular-metadata-20151217/#dfn-schema-description

## Version 0.7 and beyond...

The list above is a very tentative roadmap, but it outlines what I have on my
mind for CSVelte as of now. More to come later...
