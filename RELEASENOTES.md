## Joomla and PHP Compatibility

We are developing, testing and using Akeeba DocImport using the latest version of Joomla! and a popular and actively maintained branch of PHP 7. At the time of this writing this is:
* Joomla! 3.7
* PHP 7.0

Akeeba DocImport should be compatible with:
* Joomla! 3.4, 3.5, 3.6, 3.7
* PHP 5.4, 5.5, 5.6, 7.0, 7.1.

## Changelog

**Bug fixes**

* Could not rebuild categories consisting of multiple, single article files.
* Routing issues when there's no menu item linking to the documentation Category
* Joomla! 3.7 broke the routing due to changes in the JMenuItem class