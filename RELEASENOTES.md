## Joomla and PHP Compatibility

We are developing, testing and using Akeeba DocImport using the latest version of Joomla! and a popular and actively maintained branch of PHP 7. At the time of this writing this is:

* Joomla! 3.9
* PHP 7.2

Akeeba DocImport should be compatible with:
* Joomla! 3.8, 3.9
* PHP 5.6, 7.0, 7.1, 7.2, 7.3.

## Changelog

**Removed features**

* Removing the Search view since it was never really finished and hasn't been touched in 2 years.

**Miscellaneous changes**

* Converted to use Akeeba FEF styling instead of Bootstrap 2 (gh-24)

**Bug fixes**

* Categories with multiple, separate articles wouldn't show a list of articles
