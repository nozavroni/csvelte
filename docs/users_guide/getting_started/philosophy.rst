=================
Design Philosophy
=================

.. CSVelte's goal, ultimately, is to provide a powerful, flexible, yet easy-to-use API for reading and writing CSV data. This can be a difficult task, as often you must sacrafise ease-of-use for flexibility and/or power, or vice versa. It can be a tightrope walk. Especially as it relates to a library's API.

.. It's important to me that CSVelte's design remain pure. It isn't the biggest or most complex library out there, but what little of it there is, I want to be written *correctly*. I don't intend to sacrafice much in the way of good object-oriented design principles just to make it "simpler to understand" or "less verbose".

.. For instance, I could have eliminated the :php:class:`IO\\Stream` class and instead split up my :php:class:`Reader` class into an abstract reader and various descendant readers for reading from a local file, a PHP string, an HTTP file, etc. This would allow for much less boilerplate when instantiating a reader object. But it also would have locked me into a bizarre, rigid, dependency-based extension model. Instead I opted for composition, and although reader instantiation may be a bit more verbose, the entire system is much more flexible.

.. Why does all this matter?
.. -------------------------

.. The reason I even bothered to mention any of this is because there is often more than one way to do the same thing in CSVelte. The reason for this is that I like to design the library to be as flexible and powerful as possible, which sometimes results in quite a bit of boilerplate to accomplish relatively common tasks. And rather than sacrafise that flexibility, 
