----------------
CSVelte\\CSVelte
----------------

.. php:namespace: CSVelte

.. php:class:: CSVelte

    CSVelte Facade

    This class consists of static factory methods for easily generating commonly used objects such as readers and writers, as well as convenience methods for commonly used functionality such as exporting CSV data to a file.

    .. php:method:: reader($filename, Flavor $flavor = null)

        CSVelte\Reader Factory

        Factory method for creating a new CSVelte\Reader object Used to create a
        local file CSV reader object.

        :param $filename:
        :type $flavor: Flavor
        :param $flavor:
        :returns: CSVelte\Reader An iterator for specified CSV file

    .. php:method:: stringReader($str, Flavor $flavor = null)

        String Reader Factory

        Factory method for creating a new CSVelte\Reader object for reading from a
        PHP string

        :param $str:
        :type $flavor: Flavor
        :param $flavor:
        :returns: CSVelte\Reader An iterator for provided CSV data

    .. php:method:: writer($filename, Flavor $flavor = null)

        CSVelte\Writer Factory

        Factory method for creating a new CSVelte\Writer object for writing CSV
        data to a file. If file doesn't exist, it will be created. If it already
        contains data, it will be overwritten.

        :param $filename:
        :type $flavor: Flavor
        :param $flavor:
        :returns: CSVelte\Writer A writer object for writing to given filename

    .. php:method:: export($filename, $data, Flavor $flavor = null)

        Export CSV data to local file

        Facade method for exporting data to given filename. IF file doesn't exist
        it will be created. If it does exist it will be overwritten.

        :param $filename:
        :param $data:
        :type $flavor: Flavor
        :param $flavor:
        :returns: int Number of rows written

    .. php:method:: assertFileIsReadable($filename)

        Assert that file is readable

        Assert that a particular file exists and is readable (user has permission
        to read/access it)

        :param $filename:
        :returns: void

    .. php:method:: assertFileExists($filename)

        Assert that a particular file exists

        :param $filename:
        :returns: void
