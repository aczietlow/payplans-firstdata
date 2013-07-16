DROP TABLE IF EXISTS `#__test`;
 
CREATE TABLE `#__test` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `greeting` varchar(25) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
 
INSERT INTO `#__hello` (`greeting`) VALUES ('Hello, World!'), ('Bonjour, Monde!'), ('Ciao, Mondo!');