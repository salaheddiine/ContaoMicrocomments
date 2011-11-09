-- ********************************************************
-- *                                                      *
-- * IMPORTANT NOTE                                       *
-- *                                                      *
-- * Do not import this file manually but use the Contao  *
-- * install tool to create and maintain database tables! *
-- *                                                      *
-- ********************************************************

--
-- Table `tl_comments`
--

CREATE TABLE `tl_comments` (
  `member` int(10) unsigned NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table `tl_calendar_events`
--

CREATE TABLE `tl_calendar_events` (
  `addMicroComments` char(1) NOT NULL default '',
  `com_micro_order` varchar(32) NOT NULL default '',
  `com_micro_perPage` smallint(5) unsigned NOT NULL default '0',
  `com_micro_template` varchar(32) NOT NULL default ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table `tl_content`
--

CREATE TABLE `tl_content` (
  `com_micro_order` varchar(32) NOT NULL default '',
  `com_micro_perPage` smallint(5) unsigned NOT NULL default '0',
  `com_micro_template` varchar(32) NOT NULL default ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table `tl_module`
--

CREATE TABLE `tl_module` (
  `com_micro_order` varchar(32) NOT NULL default '',
  `com_micro_perPage` smallint(5) unsigned NOT NULL default '0',
  `com_micro_template` varchar(32) NOT NULL default ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table `tl_news`
--

CREATE TABLE `tl_news` (
  `addMicroComments` char(1) NOT NULL default '',
  `com_micro_order` varchar(32) NOT NULL default '',
  `com_micro_perPage` smallint(5) unsigned NOT NULL default '0',
  `com_micro_template` varchar(32) NOT NULL default ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;