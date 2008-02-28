CREATE TABLE IF NOT EXISTS `jos_timeclock_prefs` (
  `id` int(11) NOT NULL,
  `prefs` text,
  `published` smallint(6) NOT NULL default '0',
  `startDate` date NOT NULL default '0000-00-00',
  `endDate` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
