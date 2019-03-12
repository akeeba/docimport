## Joomla and PHP Compatibility

We are developing, testing and using Akeeba DocImport using the latest version of Joomla! and a popular and actively maintained branch of PHP 7. At the time of this writing this is:

* Joomla! 3.9
* PHP 7.2

Akeeba DocImport should be compatible with:
* Joomla! 3.8, 3.9
* PHP 5.6, 7.0, 7.1, 7.2, 7.3.

## Changelog

**Miscellaneous changes**

* Protection of all component and plugin folders against direct web access
* Do not install when the XSL extension is missing
* Do not try to process a category when the XSL extension is missing
