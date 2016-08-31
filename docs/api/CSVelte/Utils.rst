--------------
CSVelte\\Utils
--------------

.. php:namespace: CSVelte

.. php:class:: Utils

    CSVelte Utility Tool Belt

    This is a heinously ugly class full of static methods for performing various useful functions such as removing an element from an array by value, averaging the values of an erray, etc.

    *Note:* Don't get used to this class, it is almost certainly going away eventuallly

    .. php:method:: array_get($arr, $key, $default = null, $throwException = false)

        :param $arr:
        :param $key:
        :param $default:
        :param $throwException:

    .. php:method:: array_items($arr)

        :param $arr:

    .. php:method:: average($arr)

        :param $arr:

    .. php:method:: array_average($arr)

        :param $arr:

    .. php:method:: mode($arr)

        :param $arr:

    .. php:method:: array_mode($arr)

        :param $arr:

    .. php:method:: string_map($str, $callable)

        Uses array_map to apply a callback to each character in a string

        :param $str:
        :param $callable:
