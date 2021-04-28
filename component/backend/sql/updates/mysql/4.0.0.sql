/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

ALTER TABLE `#__docimport_categories` MODIFY `ordering` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__docimport_articles` MODIFY `created_on` DATETIME NULL DEFAULT NULL;
UPDATE `#__docimport_articles` SET `created_on` = NULL WHERE `created_on` = '0000-00-00 00:00:00';
ALTER TABLE `#__docimport_articles` MODIFY `modified_on` DATETIME NULL DEFAULT NULL;
UPDATE `#__docimport_articles` SET `modified_on` = NULL WHERE `modified_on` = '0000-00-00 00:00:00';
ALTER TABLE `#__docimport_articles` MODIFY `locked_on` DATETIME NULL DEFAULT NULL;
UPDATE `#__docimport_articles` SET `locked_on` = NULL WHERE `locked_on` = '0000-00-00 00:00:00';
ALTER TABLE `#__docimport_categories` MODIFY `created_on` DATETIME NULL DEFAULT NULL;
UPDATE `#__docimport_categories` SET `created_on` = NULL WHERE `created_on` = '0000-00-00 00:00:00';
ALTER TABLE `#__docimport_categories` MODIFY `modified_on` DATETIME NULL DEFAULT NULL;
UPDATE `#__docimport_categories` SET `modified_on` = NULL WHERE `modified_on` = '0000-00-00 00:00:00';
ALTER TABLE `#__docimport_categories` MODIFY `locked_on` DATETIME NULL DEFAULT NULL;
UPDATE `#__docimport_categories` SET `locked_on` = NULL WHERE `locked_on` = '0000-00-00 00:00:00';