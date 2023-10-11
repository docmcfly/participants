# 
# Table structure for table 'tx_participants_domain_model_commitment' 
# 
CREATE TABLE tx_participants_domain_model_commitment (
   uid int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
   pid int(11) DEFAULT '0' NOT NULL,
   record_type VARCHAR(255) DEFAULT '' NOT NULL,
   present SMALLINT (5) DEFAULT -1 NOT NULL,
   present_default SMALLINT (5) UNSIGNED DEFAULT 0 NOT NULL,
   event int (11) UNSIGNED DEFAULT '0',
   user int (11) UNSIGNED DEFAULT '0' NOT NULL,
   PRIMARY KEY (uid),
   KEY parent (pid),
   KEY t3ver_old (t3ver_oid, t3ver_wsid),
   KEY language (l10n_parent, sys_language_uid),
   KEY event (event),
   KEY userEvent (user, event)
);

#
# Table structure for table 'fe_users' 
#
CREATE TABLE fe_users (
   hidden_target_groups int (11) UNSIGNED DEFAULT '0' NOT NULL,
   hidden_personal_duty_roster_groups int (11) UNSIGNED DEFAULT '0' NOT NULL,
   tx_extbase_type VARCHAR (255) DEFAULT '' NOT NULL,
   apply_planning_data SMALLINT (5) UNSIGNED DEFAULT '0' NOT NULL,
   info_mail_when_personal_duty_roster_changed SMALLINT (5) UNSIGNED DEFAULT '1' NOT NULL,
   personal_duty_event_reminder SMALLINT (5) UNSIGNED DEFAULT '1' NOT NULL,
);

#
# Table structure for table 'tx_participants_user_category_mm' 
#
CREATE TABLE tx_participants_user_category_mm (
   uid_local int (11) UNSIGNED DEFAULT '0' NOT NULL,
   uid_foreign int (11) UNSIGNED DEFAULT '0' NOT NULL,
   sorting int (11) UNSIGNED DEFAULT '0' NOT NULL,
   sorting_foreign int (11) UNSIGNED DEFAULT '0' NOT NULL,
   PRIMARY KEY (uid_local, uid_foreign),
   KEY uid_local (uid_local),
   KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'tx_participants_user_hiddenpersonaldutyrostergroup_mm' 
#
CREATE TABLE tx_participants_user_hiddenpersonaldutyrostergroup_mm (
   uid_local int (11) UNSIGNED DEFAULT '0' NOT NULL,
   uid_foreign int (11) UNSIGNED DEFAULT '0' NOT NULL,
   sorting int (11) UNSIGNED DEFAULT '0' NOT NULL,
   sorting_foreign int (11) UNSIGNED DEFAULT '0' NOT NULL,
   PRIMARY KEY (uid_local, uid_foreign),
   KEY uid_local (uid_local),
   KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'tx_participants_domain_model_timeout' 
# 
CREATE TABLE tx_participants_domain_model_timeout (
   uid int(11) UNSIGNED DEFAULT '0' NOT NULL auto_increment,
   pid int(11) DEFAULT '0' NOT NULL,
   record_type VARCHAR(255) DEFAULT '' NOT NULL,
   user int (11) UNSIGNED DEFAULT '0' NOT NULL,
   from
      DATE DEFAULT '0000-00-00' NOT NULL,
      until DATE DEFAULT '0000-00-00' NOT NULL,
      reason TINYTEXT,
      PRIMARY KEY (uid),
      KEY parent (pid)
);

#
# Table structure for table 'tx_participants_domain_model_eventtype' 
# 
CREATE TABLE tx_participants_domain_model_eventtype (
   uid int(11) UNSIGNED DEFAULT '0' NOT NULL auto_increment,
   pid int(11) DEFAULT '0' NOT NULL,
   record_type VARCHAR(255) DEFAULT '' NOT NULL,
   title VARCHAR (255) DEFAULT '' NOT NULL,
   description TEXT DEFAULT '',
   usergroups SMALLINT (5) UNSIGNED DEFAULT '0' NOT NULL,
   public SMALLINT (5) UNSIGNED DEFAULT '1' NOT NULL,
   PRIMARY KEY (uid),
   KEY parent (pid)
);

#
# Table structure for table 'tx_participants_eventtype_usergroup_mm' 
# 
CREATE TABLE tx_participants_eventtype_usergroup_mm (
   uid_local int (11) UNSIGNED DEFAULT '0' NOT NULL,
   uid_foreign int (11) UNSIGNED DEFAULT '0' NOT NULL,
   sorting int (11) UNSIGNED DEFAULT '0' NOT NULL,
   sorting_foreign int (11) UNSIGNED DEFAULT '0' NOT NULL,
   PRIMARY KEY (uid_local, uid_foreign),
   KEY uid_local (uid_local),
   KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'tx_participants_domain_model_event' 
# 
CREATE TABLE tx_participants_domain_model_event (
   uid int(11) UNSIGNED DEFAULT '0' NOT NULL auto_increment,
   pid int(11) DEFAULT '0' NOT NULL,
   record_type VARCHAR(255) DEFAULT '' NOT NULL,
   title VARCHAR(1) DEFAULT '' NOT NULL,
   canceled SMALLINT (5) UNSIGNED DEFAULT '0' NOT NULL,
   description TEXT DEFAULT '',
   show_public_description SMALLINT (5) UNSIGNED DEFAULT '0' NOT NULL,
   public_description TEXT DEFAULT '',
   event_type int (11) UNSIGNED DEFAULT '0' NOT NULL,
   usergroups SMALLINT (5) UNSIGNED DEFAULT '0' NOT NULL,
   show_public_usergroups SMALLINT (5) UNSIGNED DEFAULT '0' NOT NULL,
   public_usergroups SMALLINT (5) UNSIGNED DEFAULT '0' NOT NULL,
   public SMALLINT (5) UNSIGNED DEFAULT '2' NOT NULL,
   full_day SMALLINT (5) UNSIGNED DEFAULT '0' NOT NULL,
   date date DEFAULT '2000-01-01' NOT NULL,
   time time DEFAULT '19:00:00',
   duration SMALLINT (5) UNSIGNED DEFAULT '0' NOT NULL,
   PRIMARY KEY (uid),
   KEY parent (pid)
);

#
# Table structure for table 'tx_participants_event_usergroup_mm' 
# 
CREATE TABLE tx_participants_event_usergroup_mm (
   uid_local int (11) UNSIGNED DEFAULT '0' NOT NULL,
   uid_foreign int (11) UNSIGNED DEFAULT '0' NOT NULL,
   sorting int (11) UNSIGNED DEFAULT '0' NOT NULL,
   sorting_foreign int (11) UNSIGNED DEFAULT '0' NOT NULL,
   PRIMARY KEY (uid_local, uid_foreign),
   KEY uid_local (uid_local),
   KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'tx_participants_event_publicusergroup_mm' 
# 
CREATE TABLE tx_participants_event_publicusergroup_mm (
   uid_local int (11) UNSIGNED DEFAULT '0' NOT NULL,
   uid_foreign int (11) UNSIGNED DEFAULT '0' NOT NULL,
   sorting int (11) UNSIGNED DEFAULT '0' NOT NULL,
   sorting_foreign int (11) UNSIGNED DEFAULT '0' NOT NULL,
   PRIMARY KEY (uid_local, uid_foreign),
   KEY uid_local (uid_local),
   KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'fe_groups' 
#
CREATE TABLE fe_groups (accronym VARCHAR (30) DEFAULT '',);