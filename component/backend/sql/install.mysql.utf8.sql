/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

CREATE TABLE `#__docimport_categories`
(
    `docimport_category_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `title`                 varchar(255)        NOT NULL,
    `slug`                  varchar(255)        NOT NULL,
    `description`           text                NOT NULL,
    `image`                 varchar(255)                 DEFAULT NULL,
    `process_plugins`       tinyint(3)          NOT NULL DEFAULT '0',
    `last_timestamp`        bigint(20) unsigned NOT NULL DEFAULT '0',
    `enabled`               tinyint(3)          NOT NULL DEFAULT '1',
    `ordering`              int(11)             NOT NULL DEFAULT 0,
    `created_on`            datetime            NULL     DEFAULT NULL,
    `created_by`            int(11)             NOT NULL DEFAULT '0',
    `modified_on`           datetime            NULL     DEFAULT NULL,
    `modified_by`           int(11)             NOT NULL DEFAULT '0',
    `locked_on`             datetime            NULL     DEFAULT NULL,
    `locked_by`             int(11)             NOT NULL DEFAULT '0',
    `language`              varchar(255)        NOT NULL DEFAULT '*',
    `access`                int(11)             NOT NULL DEFAULT '0',
    PRIMARY KEY (`docimport_category_id`)
) ENGINE = InnoDB
  DEFAULT COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `#__docimport_articles`
(
    `docimport_article_id`  bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `docimport_category_id` bigint(20)          NOT NULL,
    `title`                 varchar(255)        NOT NULL,
    `slug`                  varchar(255)        NOT NULL,
    `fulltext`              longtext            NOT NULL,
    `meta_description`      varchar(2048)                DEFAULT NULL,
    `meta_tags`             varchar(2048)                DEFAULT NULL,
    `last_timestamp`        int(11)                      DEFAULT NULL,
    `enabled`               tinyint(3)          NOT NULL DEFAULT '1',
    `created_on`            datetime            NULL     DEFAULT NULL,
    `created_by`            int(11)             NOT NULL DEFAULT '0',
    `modified_on`           datetime            NULL     DEFAULT NULL,
    `modified_by`           int(11)             NOT NULL DEFAULT '0',
    `locked_on`             datetime            NULL     DEFAULT NULL,
    `locked_by`             int(11)             NOT NULL DEFAULT '0',
    `ordering`              int(11)             NOT NULL DEFAULT '0',
    PRIMARY KEY (`docimport_article_id`),
    FULLTEXT INDEX `fulltextindex` (`fulltext`)
) ENGINE = InnoDB
  DEFAULT COLLATE utf8_general_ci;
