ALTER TABLE `#__joaktree_relation_events` CHANGE type type varchar(300) default NULL;
ALTER TABLE `#__joaktree_documents` CHANGE fileformat fileformat varchar(10) default NULL;
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("ELEC", "person", 34, 0, 1, 3, 0);
