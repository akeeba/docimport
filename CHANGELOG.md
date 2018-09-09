# 2.0.7

**Miscellaneous changes**

* Do not install when the XSL extension is missing
* Do not try to process a category when the XSL extension is missing

# 2.0.6

**Miscellaneous changes**

* Pre-process <imagedata> tags
* Warn the user if either FOF or FEF is not installed.
* Warn the user about incompatible versions of PHP, use of eAccelerator on PHP 5.4 and use of HHVM.
* Joomla! 3.9 backend Components menu item compatibility

# 2.0.5

**Bug fixes**

* Workaround for Joomla! Bug 16147 (https://github.com/joomla/joomla-cms/issues/16147) - Cannot access component after installation when cache is enabled
* Workaround for Joomla! bug "Sometimes files are not copied on update"

# 2.0.4

**Removed features**

* Removing the automatic update CLI script. Joomla! 3.7.0 can no longer execute extension installation under a CLI application.

**Bug fixes**

* Joomla! 3.7.0 broke backwards compatibility again, making CLI scripts fail.
* Joomla! 3.7 added a fixed width to specific button classes in the toolbar, breaking the page layout

# 2.0.3

**Bug fixes**

* Could not rebuild categories consisting of multiple, single article files.
* Routing issues when there's no menu item linking to the documentation Category
* Joomla! 3.7 broke the routing due to changes in the JMenuItem class

# 2.0.2

**Miscellaneous changes**

* Refactored CLI script

**Bug fixes**

* Typo in router doesn't let the unified search to work correctly
* Wrong category link in ATS results
* Backend Articles view: no link to Categories view
* Invalid URLs created when processing new documentation

# 2.0.1

**Bug fixes**

* Module class suffix not honored in the modules
* Router is broken in many ways

# 2.0.0

**Removed features**

* Removed the pointless control panel page

**Miscellaneous changes**

* Rewritten in FOF 3
