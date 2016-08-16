#########################
Auto-detecting CSV Flavor
#########################

If you know in advance what :ref:`flavors </reference/flavors>` of CSV you're working with, the ``CSVelte\Flavor`` class is going to work great for you. But what if you don't? Does CSV have some way of telling the developer what :ref:`flavor </reference/flavors>` of CSV it's written in? Unfortunately, no. It doesn't. But CSVelte does. Any time you read CSV data, whether it be from a local file, a string, or otherwise, CSVelte will attempt to determine the :ref:`flavor </reference/flavors>` automatically. The upshot being that in the majority of cases, you can feed CSV data to CSVelte and it will just work. 
