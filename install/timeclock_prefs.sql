CREATE TABLE IF NOT EXISTS `#__timeclock_prefs` (
  `id` int(11) NOT NULL,
  `system` text,
  `user` text,
  `admin` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

