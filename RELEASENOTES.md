## Joomla and PHP Compatibility

We are developing, testing and using Akeeba DocImport using the latest version of Joomla! and a popular and actively maintained branch of PHP 7. At the time of this writing this is:

* Joomla! 3.9
* PHP 7.3

Akeeba DocImport should be compatible with:
* Joomla! 3.9, 4.0
* PHP 7.1, 7.2, 7.3, 7.4, 8.0.

At the time of this writing PHP 8.0 has not been released yet. As a result support for it is considered tentative.

## Changelog

**Miscellaneous changes**

* Replace zero datetime with nullable datetime (gh-26)
* Update URLs to point to akeeba.com
* Update CDN URL
* PHP version warning can now handle "too new" PHP versions
* Improved unhandled PHP exception error page
