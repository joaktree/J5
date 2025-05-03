CREATE TABLE IF NOT EXISTS #__joaktree_admin_persons (
app_id           tinyint(4)            NOT NULL ,
id               varchar(20)           NOT NULL ,
default_tree_id  int(11)      default  NULL ,
published        tinyint(1)            NOT NULL ,
access           int(11)      unsigned NOT NULL default 0,
living           tinyint(1)            NOT NULL default 0,
page             tinyint(1)            NULL  default 0,
robots           tinyint(2)            NOT NULL default 0,
map              tinyint(1)            NOT NULL default 0 ,
PRIMARY KEY  (app_id, id) 
)  ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Joaktree Admin person';

CREATE TABLE IF NOT EXISTS #__joaktree_applications ( 
id               tinyint(4)  unsigned  NOT NULL auto_increment,
asset_id         int(10)      unsigned NOT NULL default 0,
title            varchar(30)           NOT NULL,
description      varchar(100)          NOT NULL,
programName      varchar(30)           NOT NULL,
params           varchar(2048)         NOT NULL,
PRIMARY KEY  (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Joaktree Application';

CREATE TABLE IF NOT EXISTS #__joaktree_citations ( 
objectType       enum( 'person' , 'personName', 'personEvent',
			            'personNote','personDocument',
                        'relation','relationEvent','relationNote')  NOT NULL,
objectOrderNumber smallint(2)          NOT NULL default 0, 
app_id           tinyint(4)            NOT NULL,
person_id_1      varchar(20)           NOT NULL,
person_id_2      varchar(20)           NOT NULL default 'EMPTY',
source_id        varchar(20)           NOT NULL,
orderNumber      smallint(2)           NOT NULL,
dataQuality      tinyint(2)                NULL,
page             varchar(250)  default     NULL,
quotation        varchar(250)  default     NULL,
note             varchar(250)  default     NULL,
PRIMARY KEY  (objectType,objectOrderNumber,app_id,person_id_1,person_id_2,source_id,orderNumber),
        KEY person_id (app_id,person_id_1) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Joaktree Citations';

CREATE TABLE IF NOT EXISTS #__joaktree_display_settings( 
id               int(11)      unsigned NOT NULL auto_increment,
code             varchar(4)            NOT NULL,
level            enum('person','name','relation') NOT NULL,
ordering         tinyint(3)            NOT NULL,
published        tinyint(1)   unsigned NOT NULL default 0 ,
access           tinyint(3)   unsigned NOT NULL default 0,
accessLiving     tinyint(3)   unsigned NOT NULL default 0,
altLiving        tinyint(3)            NOT NULL default 0,
PRIMARY KEY  (id),
UNIQUE KEY UK_CODE_LEVEL (code, level) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Joaktree Display Setting';

INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("ENGA", "relation", 1, 1, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("MARB", "relation", 2, 1, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("MARC", "relation", 3, 1, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("MARS", "relation", 4, 1, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("MARL", "relation", 5, 0, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("MARR", "relation", 6, 1, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("ANUL", "relation", 7, 0, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("DIV",  "relation", 8, 1, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("NCHI", "relation", 9, 0, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("EVEN", "relation", 10, 0, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("ENOT", "relation", 11, 1, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("ESOU", "relation", 12, 1, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("CAST", "person", 1, 0, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("TITL", "person", 2, 1, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("BIRT", "person", 3, 1, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("BAPM", "person", 4, 1, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("BRTM", "person", 5, 1, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("CHR",  "person", 6, 1, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("BLES", "person", 7, 0, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("BARM", "person", 8, 0, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("BASM", "person", 9, 0, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("CONF", "person", 10, 0, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("ADOP", "person", 11, 0, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("CHRA", "person", 12, 0, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("DEAT", "person", 13, 1, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("BURI", "person", 14, 1, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("CREM", "person", 15, 1, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("YART", "person", 16, 0, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("FCOM", "person", 17, 0, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("EDUC", "person", 18, 0, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("GRAD", "person", 19, 0, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("OCCU", "person", 20, 1, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("RETI", "person", 21, 0, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("EMIG", "person", 22, 1, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("IMMI", "person", 23, 1, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("NATU", "person", 24, 1, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("NATI", "person", 25, 0, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("RESI", "person", 26, 1, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("RELI", "person", 27, 0, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("DSCR", "person", 28, 0, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("EVEN", "person", 29, 0, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("NOTE", "person", 30, 1, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("ENOT", "person", 31, 1, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("SOUR", "person", 32, 1, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("ESOU", "person", 33, 1, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("ELEC", "person", 34, 0, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("NAME", "name", 1, 1, 1, 1, 1);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("GIVN", "name", 2, 0, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("NICK", "name", 3, 0, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("ADPN", "name", 4, 0, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("AKA",  "name", 5, 1, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("BIRN", "name", 6, 0, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("CENN", "name", 7, 0, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("CURN", "name", 8, 0, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("FRKA", "name", 9, 0, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("HEBN", "name", 10, 0, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("INDG", "name", 11, 0, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("MARN", "name", 12, 1, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("RELN", "name", 13, 0, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("OTHN", "name", 14, 0, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("NOTE", "name", 15, 1, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("SOUR", "name", 16, 1, 1, 3, 0);
INSERT IGNORE INTO #__joaktree_display_settings (code, level, ordering, published, access, accessLiving, altLiving ) VALUES ("SURN", "name", 17, 0, 1, 3, 0);

CREATE TABLE IF NOT EXISTS #__joaktree_documents ( 
app_id           tinyint(4)            NOT NULL,
id               varchar(20)           NOT NULL,
file             varchar(200)          NOT NULL,
fileformat       varchar(10)           NULL,
indCitation      tinyint(1)   unsigned default 0,
note_id          varchar(20)               NULL,
title            varchar(100)              NULL,
note             text         default      NULL,
PRIMARY KEY  (app_id,id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Joaktree Documents';

CREATE TABLE IF NOT EXISTS #__joaktree_gedcom_objectlines(
id               int(11)      unsigned NOT NULL auto_increment,
object_id        varchar(20)           NOT NULL ,
order_nr         int(11)               NOT NULL ,
level            int(11)               NOT NULL ,
tag              varchar(20)           NOT NULL ,
value            text         default      NULL ,
subtype          enum( 'spouse','partner','natural','adopted',
                        'step','foster','legal')     NULL,
PRIMARY KEY  (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Joaktree Gedcom Objectlines';

CREATE TABLE IF NOT EXISTS #__joaktree_gedcom_objects (
id               int(11)      unsigned NOT NULL auto_increment,
tag              varchar(4)            NOT NULL,
value            varchar(50)  default  NULL,
PRIMARY KEY  (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Joaktree Gedcom Objects';

CREATE TABLE IF NOT EXISTS #__joaktree_locations (
id               int(11)      unsigned NOT NULL AUTO_INCREMENT ,
indexLoc         varchar(1)            NOT NULL ,
value            varchar(300)           NOT NULL ,
latitude         decimal(10,7)             NULL ,
longitude        decimal(10,7)             NULL ,
indServerProcessed tinyint(1) unsigned NOT NULL default 0 ,
indDeleted       tinyint(1)   unsigned NOT NULL default 0 ,
results          tinyint(2)   unsigned     NULL ,
resultValue      varchar(300)              NULL,
PRIMARY KEY  (id),
KEY indexLoc (indexLoc) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Joaktree Locations';

CREATE TABLE IF NOT EXISTS #__joaktree_logremovals (
id               int(10)      unsigned NOT NULL AUTO_INCREMENT ,
app_id           tinyint(4)   unsigned NOT NULL ,
object_id        varchar(20)           NOT NULL ,
object           enum('prsn','sour','repo','docu','note') NOT NULL ,
description      varchar(100)          NOT NULL ,
PRIMARY KEY  (id) ,
KEY objectIndex2 (app_id,object_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Joaktree Logremovals';

CREATE TABLE IF NOT EXISTS #__joaktree_logs (
id               int(10)      unsigned NOT NULL AUTO_INCREMENT ,
app_id           tinyint(4)   unsigned NOT NULL ,
object_id        varchar(20)           NOT NULL ,
object           enum( 'prsn','sour','repo','docu','note') NOT NULL ,
changeDateTime   datetime              NOT NULL ,
logevent         varchar(9)            NOT NULL ,
user_id          int(11)               NOT NULL ,
PRIMARY KEY  (id) ,
KEY objectIndex1 (app_id,object_id) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Joaktree Logs';

CREATE TABLE IF NOT EXISTS #__joaktree_maps (
id              int(11)      unsigned NOT NULL AUTO_INCREMENT ,
name            varchar(50)           NOT NULL ,
selection       enum( 'tree','person','location' ) NOT NULL ,
service         varchar(20)           NOT NULL default 'staticmap',
app_id          tinyint(4)   unsigned NOT NULL ,
relations       tinyint(1)   unsigned NOT NULL default 0 ,
params          varchar(2048)         NOT NULL ,
tree_id         tinyint(4)   unsigned     NULL ,
person_id       varchar(20)               NULL ,
subject         varchar(50)               NULL ,
period_start    int(11)      unsigned     NULL ,
period_end      int(11)      unsigned     NULL ,
excludePersonEvents   varchar(200)         NULL ,
excludeRelationEvents varchar(200)         NULL ,
PRIMARY KEY  (id) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Joaktree Maps';

CREATE TABLE IF NOT EXISTS #__joaktree_notes (
app_id           tinyint(4)            NOT NULL ,
id               varchar(20)           NOT NULL ,
value            text                      NULL ,
PRIMARY KEY  (app_id,id) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Joaktree Notes';

CREATE TABLE IF NOT EXISTS #__joaktree_persons (
app_id           tinyint(4)            NOT NULL ,
id               varchar(20)           NOT NULL ,
indexNam         varchar(1)            NOT NULL ,
firstName        varchar(100)           NOT NULL ,
patronym         varchar(100)  default      NULL ,
namePreposition  varchar(15)  default      NULL ,
familyName       varchar(50)           NOT NULL ,
sex              char(1)               NOT NULL ,
indNote          tinyint(1)   unsigned default 0 ,
indCitation      tinyint(1)   unsigned default 0 ,
indHasParent     tinyint(1)   unsigned NOT NULL default 0 ,
indHasPartner    tinyint(1)   unsigned NOT NULL default 0 ,
indHasChild      tinyint(1)   unsigned NOT NULL default 0 ,
lastUpdateTimeStamp timestamp             NOT NULL 
                 DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
indIsWitness     tinyint(1)   unsigned NOT NULL default 0 ,
prefix           varchar(20)               NULL ,
suffix           varchar(20)               NULL ,
PRIMARY KEY  (app_id,id) ,
KEY IndexNam (indexNam) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Joaktree Persons';

CREATE TABLE IF NOT EXISTS #__joaktree_person_documents (
app_id           tinyint(4)            NOT NULL ,
person_id        varchar(20)           NOT NULL ,
document_id      varchar(20)           NOT NULL ,
PRIMARY KEY  (app_id,person_id,document_id) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Joaktree Persons Documents';

CREATE TABLE IF NOT EXISTS #__joaktree_person_events (
app_id           tinyint(4)            NOT NULL ,
person_id        varchar(20)           NOT NULL ,
orderNumber      smallint(2)           NOT NULL ,
code             varchar(4)            NOT NULL ,
indNote          tinyint(1)   unsigned default 0 ,
indCitation      tinyint(1)   unsigned default 0 ,
type             varchar(300)  default      NULL ,
eventDate        varchar(40)  default      NULL ,
loc_id           int(11)      default      NULL ,
location         varchar(300)  default  	  NULL ,
value            varchar(300) default      NULL ,
PRIMARY KEY  (app_id,person_id,orderNumber) ,
KEY LOC1     (location) ,
KEY LOI1     (loc_id) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Joaktree Persons Events';

CREATE TABLE IF NOT EXISTS #__joaktree_person_names ( 
app_id           tinyint(4)            NOT NULL ,
person_id        varchar(20)           NOT NULL ,
orderNumber      smallint(2)           NOT NULL ,
code             varchar(4)            NOT NULL ,
indNote          tinyint(1)   unsigned default 0 ,
indCitation      tinyint(1)   unsigned default 0 ,
eventDate        varchar(40)  default      NULL ,
value            varchar(100) default      NULL ,
PRIMARY KEY  (app_id,person_id,orderNumber) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Joaktree Persons Names';

CREATE TABLE IF NOT EXISTS #__joaktree_person_notes (
app_id           tinyint(4)            NOT NULL ,
person_id        varchar(20)           NOT NULL ,
orderNumber      smallint(2)           NOT NULL ,
indCitation      tinyint(1)   unsigned default 0 ,
nameOrderNumber  smallint(2)  default      NULL ,
eventOrderNumber smallint(2)  default      NULL ,
note_id          varchar(20)               NULL ,
value            text ,
PRIMARY KEY  (app_id,person_id,orderNumber) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Joaktree Persons Notes';

CREATE TABLE IF NOT EXISTS #__joaktree_registry_items (
id               int(11)      unsigned NOT NULL auto_increment ,
regkey           varchar(255)          NOT NULL ,
value            varchar(2048)         NOT NULL ,
PRIMARY KEY  (id) ,
UNIQUE KEY UK_KEY (regkey)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Joaktree Registry Items';

INSERT IGNORE INTO #__joaktree_registry_items (regkey, value) VALUES ("LAST_UPDATE_DATETIME", NOW() );
INSERT IGNORE INTO #__joaktree_registry_items (regkey, value) VALUES ("INITIAL_CHAR", "0" );
INSERT IGNORE INTO #__joaktree_registry_items (regkey, value) VALUES ("VERSION", "'.$version.'" );

CREATE TABLE IF NOT EXISTS #__joaktree_relations (
app_id           tinyint(4)            NOT NULL ,
person_id_1      varchar(20)           NOT NULL ,
person_id_2      varchar(20)           NOT NULL ,
type             enum('partner','father','mother') NOT NULL ,
subtype          enum('spouse','partner','natural','adopted','step','foster','legal') NULL ,
family_id        varchar(20)           NOT NULL ,
indNote          tinyint(1)   unsigned default 0 ,
indCitation      tinyint(1)   unsigned default 0 ,
orderNumber_1    smallint(2)  default      NULL ,
orderNumber_2    smallint(2)  default      NULL ,
PRIMARY KEY  (app_id,person_id_1,person_id_2) ,
KEY person_id (app_id,person_id_1) ,
KEY to_person_id (app_id,person_id_2) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Joaktree Relations';

CREATE TABLE IF NOT EXISTS #__joaktree_relation_events (
app_id           tinyint(4)            NOT NULL ,
person_id_1      varchar(20)           NOT NULL ,
person_id_2      varchar(20)           NOT NULL ,
orderNumber      smallint(2)           NOT NULL ,
code             varchar(4)            NOT NULL ,
indNote          tinyint(1)   unsigned default 0 ,
indCitation      tinyint(1)   unsigned default 0 ,
type             varchar(300) default      NULL ,
eventDate        varchar(40)  default      NULL ,
loc_id           int(11)      default      NULL ,
location         varchar(300) default     NULL ,
value            varchar(100) default      NULL ,
PRIMARY KEY  (app_id,person_id_1,person_id_2,orderNumber) ,
KEY LOC2     (location) ,
KEY LOI2     (loc_id) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Joaktree Relation Events';

CREATE TABLE IF NOT EXISTS #__joaktree_relation_notes (
app_id           tinyint(4)            NOT NULL ,
person_id_1      varchar(20)           NOT NULL ,
person_id_2      varchar(20)           NOT NULL ,
orderNumber      smallint(2)           NOT NULL ,
indCitation      tinyint(1)   unsigned default 0 ,
eventOrderNumber smallint(2)  default      NULL ,
note_id          varchar(20)               NULL ,
value            text ,
PRIMARY KEY  (app_id,person_id_1,person_id_2,orderNumber)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Joaktree Relation Notes';

CREATE TABLE IF NOT EXISTS #__joaktree_repositories (
app_id           tinyint(4)            NOT NULL ,
id               varchar(20)           NOT NULL ,
name             varchar(50)           NOT NULL ,
website          varchar(100) default  NULL ,
PRIMARY KEY  (app_id,id) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Joaktree Repositories';

CREATE TABLE IF NOT EXISTS #__joaktree_sources (
app_id           tinyint(4)            NOT NULL ,
id               varchar(20)           NOT NULL ,
repo_id          varchar(20)  default  NULL ,
title            varchar(500) default  NULL ,
abbr             varchar(250) default  NULL ,
media            varchar(100) DEFAULT  '',
note             text NULL,
www              varchar(250) NULL DEFAULT '',
author           varchar(250) default  NULL ,
publication      varchar(250) default  NULL ,
information      text         default  NULL ,
PRIMARY KEY  (app_id,id) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Joaktree Sources';

CREATE TABLE IF NOT EXISTS #__joaktree_themes (
id               smallint(6)  unsigned NOT NULL auto_increment ,
name             varchar(25)  default  NULL ,
home             tinyint(1)   unsigned NOT NULL default 0 ,
params           varchar(2048)         NOT NULL ,
PRIMARY KEY  (id) ,
UNIQUE KEY UKNAME (name) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Joaktree Themes';

INSERT IGNORE INTO `#__joaktree_themes` (`id`, `name`, `home`, `params`) VALUES
(1, 'Joaktree', 1, '{\"search_width\":\"120\",\"show_update\":\"Y\",\"columns\":\"3\",\"groupCount\":\"3\",\"abbreviation\":\"\",\"lineage\":\"3\",\"Directory\":\"images/joaktree\",\"pxHeight\":\"135\",\"pxWidth\":\"325\",\"transDelay\":\"50\",\"nextDelay\":\"5000\",\"TitleSlideshow\":\"A Genealogy Slideshow\",\"Sequence\":\"3\",\"pxMapWidth\":\"700\",\"statMarkerColor\":\"\",\"descendantchart\":\"1\",\"descendantlevel\":\"20\",\"ancestorchart\":\"1\",\"ancestorlevel\":\"1\",\"ancestordates\":\"1\",\"indTabBehavior\":\"1\",\"notetitlelength\":\"30\"}'),
(2, 'Blue', 0, '{\"search_width\":\"120\",\"show_update\":\"Y\",\"columns\":\"3\",\"groupCount\":\"3\",\"abbreviation\":\"\",\"lineage\":\"3\",\"columnsLoc\":\"3\",\"groupCountLoc\":\"3\",\"Directory\":\"images\\/joaktree\",\"pxHeight\":\"135\",\"pxWidth\":\"325\",\"transDelay\":\"50\",\"nextDelay\":\"5000\",\"indTitle\":\"0\",\"TitleSlideshow\":\"A Genealogy Slideshow\",\"Sequence\":\"3\",\"pxMapWidth\":\"700\",\"statMarkerColor\":\"\",\"dynMarkerIcons\":\"\",\"descendantchart\":\"1\",\"descendantlevel\":\"20\",\"ancestorchart\":\"1\",\"ancestorlevel\":\"1\",\"ancestordates\":\"1\",\"indTabBehavior\":\"1\",\"notetitlelength\":\"30\"}'),
(3, 'Green', 0, '{\"search_width\":\"120\",\"show_update\":\"Y\",\"columns\":\"3\",\"groupCount\":\"3\",\"abbreviation\":\"\",\"lineage\":\"3\",\"Directory\":\"images/joaktree\",\"pxHeight\":\"135\",\"pxWidth\":\"325\",\"transDelay\":\"50\",\"nextDelay\":\"5000\",\"TitleSlideshow\":\"A Genealogy Slideshow\",\"Sequence\":\"3\",\"pxMapWidth\":\"700\",\"statMarkerColor\":\"\",\"descendantchart\":\"1\",\"descendantlevel\":\"20\",\"ancestorchart\":\"1\",\"ancestorlevel\":\"1\",\"ancestordates\":\"1\",\"indTabBehavior\":\"1\",\"notetitlelength\":\"30\"}'),
(4, 'Red', 0, '{\"search_width\":\"120\",\"show_update\":\"Y\",\"columns\":\"3\",\"groupCount\":\"3\",\"abbreviation\":\"\",\"lineage\":\"3\",\"Directory\":\"images/joaktree\",\"pxHeight\":\"135\",\"pxWidth\":\"325\",\"transDelay\":\"50\",\"nextDelay\":\"5000\",\"TitleSlideshow\":\"A Genealogy Slideshow\",\"Sequence\":\"3\",\"pxMapWidth\":\"700\",\"statMarkerColor\":\"\",\"descendantchart\":\"1\",\"descendantlevel\":\"20\",\"ancestorchart\":\"1\",\"ancestorlevel\":\"1\",\"ancestordates\":\"1\",\"indTabBehavior\":\"1\",\"notetitlelength\":\"30\"}');

                
CREATE TABLE IF NOT EXISTS #__joaktree_trees (
id               int(10)      unsigned NOT NULL auto_increment ,
app_id           tinyint(4)            NOT NULL ,
asset_id         int(10)      unsigned NOT NULL default 0,
holds            enum('all','descendants') NOT NULL default 'all' ,
published        tinyint(1)            NOT NULL default 1 ,
access           int(11)      unsigned NOT NULL default 1 ,
name             varchar(250)          NOT NULL ,
theme_id         int(11)               NOT NULL ,
indGendex        tinyint(1)   unsigned NOT NULL default 0 ,
indPersonCount   tinyint(1)   unsigned NOT NULL default 0 ,
indMarriageCount tinyint(1)   unsigned NOT NULL default 0 ,
robots           tinyint(2)            NOT NULL default 0 , 
root_person_id   varchar(20)               NULL ,
catid            int(11)                   NULL ,
PRIMARY KEY  (id) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Joaktree Trees';

CREATE TABLE IF NOT EXISTS #__joaktree_tree_persons (
id               varchar(31)           NOT NULL ,
app_id           tinyint(4)            NOT NULL ,
tree_id          int(11)               NOT NULL ,
person_id        varchar(20)           NOT NULL ,
type             enum( 'R','P','C')    NOT NULL ,
lineage          varchar(250) default  NULL ,
PRIMARY KEY  (id) ,
KEY person_id (app_id,person_id) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Joaktree Tree persons';