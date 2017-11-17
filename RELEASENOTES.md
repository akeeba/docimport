## Joomla and PHP Compatibility

We are developing, testing and using Akeeba DocImport using the latest version of Joomla! and a popular and actively maintained branch of PHP 7. At the time of this writing this is:
* Joomla! 3.7
* PHP 7.0

Akeeba DocImport should be compatible with:
* Joomla! 3.4, 3.5, 3.6, 3.7
* PHP 5.4, 5.5, 5.6, 7.0, 7.1.

## Language files

DocImport comes with English (Great Britain) language built-in. Installation packages for other languages are available [on our language download page](https://cdn.akeebabackup.com/language/docimport/index.html).

## Changelog

**Removed features**

* Removing the automatic update CLI script. Joomla! 3.7.0 can no longer execute extension installation under a CLI application.

**Bug fixes**

* Joomla! 3.7.0 broke backwards compatibility again, making CLI scripts fail.
* Joomla! 3.7 added a fixed width to specific button classes in the toolbar, breaking the page layout
