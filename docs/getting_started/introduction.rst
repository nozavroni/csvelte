############
Introduction
############

CSVelte is a modern, object-oriented :abbr:`CSV (Comma Separated Values)` library for :abbr:`PHP (PHP: Hypertext Preprocessor)` 5.6+ (although the minimum version will likely change in the near future).

A little history
================

CSVelte was originally written in 2008 as PHP CSV Utilities (or PCU). At the time, CSV seemed to be the format of choice for the majority of my clients. I had a lot of code that relied on PHP's native CSV functions: ``fgetcsv``, ``fputcsv``, and ``str_getcsv``, but I recall quickly growing weary of their quirks and shortcomings. So, after scouring the internet for a decent CSV library and coming up short, I decided to try my hand at writing one myself. Taking my inspiration from Python's native CSV module, I slapped together and released the first version of PHP CSV Utilities. Unfortunately I got busy with other projects and PCU was abandoned after only a few short months.

Over the years, I've received a surprising amount of e-mails about the library and have even seen it integrated into several open source projects. It's become obvious to me, based on these facts, that there is definitely a demand for such a library. So I threw together a few classes, cannibalizing the old library for parts, packaged the whole thing up nicely and re-released it as CSVelte.
