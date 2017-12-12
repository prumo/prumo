CREATE TABLE prumo_config
(
  config_name VARCHAR(40) NOT NULL,
  config_value VARCHAR(255),
  CONSTRAINT config_pkey PRIMARY KEY (config_name)
);

CREATE TABLE prumo_syslogin
(
  username VARCHAR(30) NOT NULL,
  fullname VARCHAR(50) NOT NULL,
  password VARCHAR(40),
  enabled VARCHAR(1) NOT NULL DEFAULT 't',
  CONSTRAINT syslogin_pkey PRIMARY KEY (username)
);

CREATE TABLE prumo_groups
(
  enabled VARCHAR(1) NOT NULL DEFAULT 't',
  groupname VARCHAR(30) NOT NULL,
  CONSTRAINT groups_pkey PRIMARY KEY (groupname)
);

CREATE TABLE prumo_groups_syslogin
(
  groupname VARCHAR(30) NOT NULL,
  username VARCHAR(30) NOT NULL,
  CONSTRAINT groups_syslogin_pkey PRIMARY KEY (groupname, username)
);

CREATE TABLE prumo_routines
(
  routine VARCHAR(40) NOT NULL,
  enabled VARCHAR(1) NOT NULL DEFAULT 't',
  audit VARCHAR(1) NOT NULL DEFAULT 't',
  link VARCHAR(255),
  description text,
  menu_parent VARCHAR(40),
  menu_label VARCHAR(40),
  menu_icon VARCHAR(255),
  type VARCHAR(40),
  CONSTRAINT routines_pkey PRIMARY KEY (routine)
);

CREATE TABLE prumo_routines_groups
(
  routine VARCHAR(40) NOT NULL,
  groupname VARCHAR(40) NOT NULL,
  c VARCHAR(1) NOT NULL DEFAULT 'f',
  r VARCHAR(1) NOT NULL DEFAULT 'f',
  u VARCHAR(1) NOT NULL DEFAULT 'f',
  d VARCHAR(1) NOT NULL DEFAULT 'f',
  CONSTRAINT routines_groups_pkey PRIMARY KEY (routine, groupname),
  CONSTRAINT routines_groups_routine_fkey FOREIGN KEY (routine)
      REFERENCES prumo_routines (routine) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE prumo_acess_denied
(
  id integer PRIMARY KEY AUTOINCREMENT,
  username VARCHAR(30) NOT NULL,
  routine VARCHAR(40) NOT NULL,
  permission VARCHAR(10) NOT NULL,
  date_time DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT acess_denied_routine_fkey FOREIGN KEY (routine)
      REFERENCES prumo_routines (routine) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT acess_denied_username_fkey FOREIGN KEY (username)
      REFERENCES prumo_syslogin (username) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE prumo_log_sql
(
  log_serial integer PRIMARY KEY AUTOINCREMENT,
  log_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
  log_obj_name VARCHAR(100) NOT NULL,
  usr_login VARCHAR(40),
  log_prumo_method VARCHAR(40) NOT NULL,
  log_statement text NOT NULL,
  routine VARCHAR(40),
  CONSTRAINT log_sql_routine_fkey FOREIGN KEY (routine)
      REFERENCES prumo_routines (routine) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT log_sql_usr_login_fkey FOREIGN KEY (usr_login)
      REFERENCES prumo_syslogin (username) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE prumo_update_framework
(
   file_name VARCHAR(100) NOT NULL, 
   usr_login VARCHAR(40), 
   date_time DATETIME DEFAULT CURRENT_TIMESTAMP, 
   PRIMARY KEY (file_name)
);

CREATE TABLE prumo_reminder
(
	id integer PRIMARY KEY AUTOINCREMENT,
	event VARCHAR(30) NOT NULL,
	description VARCHAR NOT NULL,
	reminder_date DATE NOT NULL,
	repeat_every integer,
	repeat_interval VARCHAR(10),
	username VARCHAR(30),
	last_seen DATE NOT NULL DEFAULT CURRENT_DATE,
	FOREIGN KEY (username) REFERENCES prumo_syslogin (username) MATCH SIMPLE ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE prumo_active_reminder
(
	id integer PRIMARY KEY NOT NULL,
	reminder_date DATE NOT NULL,
	show_at DATETIME NOT NULL,
	FOREIGN KEY (id) REFERENCES prumo_reminder (id) MATCH SIMPLE ON UPDATE CASCADE ON DELETE CASCADE
);

INSERT INTO prumo_routines (routine,enabled,audit,type) VALUES ('prumo_reminders','t','f','view');
UPDATE prumo_routines SET routine='prumo_reminders',link='prumo/view_reminder.php',description=NULL,menu_parent='prumo_system',menu_label='Lembretes',menu_icon='prumo/images/icons/reminder.png' WHERE routine='prumo_reminders';
INSERT INTO prumo_routines_groups (routine,groupname,c,r,u,d) VALUES ('prumo_reminders','sysadmin','t','t','t','t');

INSERT INTO prumo_routines (routine, enabled, type, menu_label, menu_icon, audit) VALUES ('prumo_system', 't', 'root_menu', 'SISTEMA', 'prumo/images/icons/example.png', 'f');
INSERT INTO prumo_routines (routine, enabled, type, menu_parent, menu_label, menu_icon, audit) VALUES ('prumo_access_control', 't', 'root_menu', 'prumo_system', 'Controle de Acesso', 'prumo/images/icons/example.png', 'f');

INSERT INTO prumo_routines (routine, link, enabled, type, menu_parent, menu_label, menu_icon, audit) VALUES ('prumo_groups',         'prumo/view_groups.php',          't', 'view', 'prumo_access_control', 'Grupos de Usuários',   'prumo/images/icons/users.png', 'f');
INSERT INTO prumo_routines (routine, link, enabled, type, menu_parent, menu_label, menu_icon, audit) VALUES ('prumo_controlPanel',   'prumo/view_control_panel.php',   't', 'view', 'prumo_system',         'Painel de Controle',   'prumo/images/icons/system.png', 'f');
INSERT INTO prumo_routines (routine, link, enabled, type, menu_parent, menu_label, menu_icon, audit) VALUES ('prumo_changePassword', 'prumo/view_change_password.php', 't', 'view', 'prumo_system',         'Alterar Senha',        'prumo/images/icons/key.png', 'f');
INSERT INTO prumo_routines (routine, link, enabled, type, menu_parent, menu_label, menu_icon, audit) VALUES ('prumo_about',          'prumo/view_about.php',           't', 'view', 'prumo_system',         'Sobre o Prumo',        'prumo/images/icons/exclamation.png', 'f');
INSERT INTO prumo_routines (routine, link, enabled, type, menu_parent, menu_label, menu_icon, audit) VALUES ('prumo_users',          'prumo/view_users.php',           't', 'view', 'prumo_access_control', 'Usuários',             'prumo/images/icons/users.png', 'f');
INSERT INTO prumo_routines (routine, link, enabled, type, menu_parent, menu_label, menu_icon, audit) VALUES ('prumo_routines',       'prumo/view_routines.php',        't', 'view', 'prumo_access_control', 'Rotinas e permissões', 'prumo/images/icons/users.png', 'f');
INSERT INTO prumo_routines (routine, link, enabled, type, menu_parent, menu_label, menu_icon, audit) VALUES ('prumo_submission',     'prumo/view_submission.php',      't', 'view', 'prumo_system',         'Instruções iniciais',  'prumo/images/icons/exclamation.png', 'f');
INSERT INTO prumo_routines (routine, link, enabled, type, menu_parent, menu_label, menu_icon, audit) VALUES ('prumo_update',         'prumo/view_update.php',          't', 'view', 'prumo_system',         'Atualização',          'prumo/images/icons/system.png', 'f');
INSERT INTO prumo_routines (routine, link, enabled, type, menu_parent, menu_label, menu_icon, audit) VALUES ('prumo_devtools',       'prumo/ctrl_devtools.php',        't', 'view', 'prumo_system',         'Desenvolvimento',      'prumo/images/icons/system.png', 'f');

INSERT INTO prumo_syslogin(username, fullname, "password") VALUES ('admin', 'Administrador do sistema', '21232f297a57a5a743894a0e4a801fc3');

INSERT INTO prumo_groups(enabled, groupname) VALUES ('t', 'sysadmin');
INSERT INTO prumo_groups(enabled, groupname) VALUES ('t', 'dev');
INSERT INTO prumo_groups_syslogin(groupname, username) VALUES ('sysadmin', 'admin');
INSERT INTO prumo_groups_syslogin(groupname, username) VALUES ('dev', 'admin');

INSERT INTO prumo_routines_groups(routine, groupname, c, r, u, d) VALUES ('prumo_groups', 'sysadmin', 't', 't', 't', 't');
INSERT INTO prumo_routines_groups(routine, groupname, c, r, u, d) VALUES ('prumo_controlPanel', 'sysadmin', 't', 't', 't', 't');
INSERT INTO prumo_routines_groups(routine, groupname, c, r, u, d) VALUES ('prumo_changePassword', 'sysadmin', 't', 't', 't', 't');
INSERT INTO prumo_routines_groups(routine, groupname, c, r, u, d) VALUES ('prumo_about', 'sysadmin', 't', 't', 't', 't');
INSERT INTO prumo_routines_groups(routine, groupname, c, r, u, d) VALUES ('prumo_system', 'sysadmin', 't', 't', 't', 't');
INSERT INTO prumo_routines_groups(routine, groupname, c, r, u, d) VALUES ('prumo_users', 'sysadmin', 't', 't', 't', 't');
INSERT INTO prumo_routines_groups(routine, groupname, c, r, u, d) VALUES ('prumo_routines', 'sysadmin', 't', 't', 't', 't');
INSERT INTO prumo_routines_groups(routine, groupname, c, r, u, d) VALUES ('prumo_submission', 'sysadmin', 't', 't', 't', 't');
INSERT INTO prumo_routines_groups(routine, groupname, c, r, u, d) VALUES ('prumo_access_control', 'sysadmin', 't', 't', 't', 't');
INSERT INTO prumo_routines_groups(routine, groupname, c, r, u, d) VALUES ('prumo_update', 'sysadmin', 't', 't', 't', 't');
INSERT INTO prumo_routines_groups(routine, groupname, c, r, u, d) VALUES ('prumo_devtools', 'dev', 't', 't', 't', 't');

INSERT INTO prumo_groups(enabled, groupname) VALUES ('t', 'change_password');
INSERT INTO prumo_routines_groups(routine, groupname, c, r, u, d) VALUES ('prumo_system', 'change_password', 'f', 't', 'f', 'f');
INSERT INTO prumo_routines_groups(routine, groupname, c, r, u, d) VALUES ('prumo_changePassword', 'change_password', 'f', 't', 't', 'f');

CREATE VIEW prumo_v_menu AS
SELECT routine,tree,type FROM
(
	SELECT
		routine,
		CASE
			WHEN level2='_' THEN level1
			WHEN level3='_' THEN level1||' > '||level2
			WHEN level4='_' THEN level1||' > '||level2||' > '||level3
			WHEN level5='_' THEN level1||' > '||level2||' > '||level3||' > '||level4
			ELSE level1||' > '||level2||' > '||level3||' > '||level4||' > '||level5
		END as tree,
		type
	FROM
	(
		SELECT 
			routine,
			(SELECT menu_label FROM prumo_routines WHERE routine=(SELECT menu_parent FROM prumo_routines WHERE routine=(SELECT menu_parent FROM prumo_routines WHERE routine=(SELECT menu_parent FROM prumo_routines WHERE routine=m5.menu_parent)))) as level1,
			(SELECT menu_label FROM prumo_routines WHERE routine=(SELECT menu_parent FROM prumo_routines WHERE routine=(SELECT menu_parent FROM prumo_routines WHERE routine=m5.menu_parent))) as level2,
			(SELECT menu_label FROM prumo_routines WHERE routine=(SELECT menu_parent FROM prumo_routines WHERE routine=m5.menu_parent)) as level3,
			(SELECT menu_label FROM prumo_routines WHERE routine=m5.menu_parent) as level4,
			menu_label as level5,
			5 as nivel,
			type
		FROM prumo_routines m5
		WHERE m5.routine IN (
			--level5
			SELECT routine FROM prumo_routines WHERE menu_parent IN (
				--level4
				SELECT routine FROM prumo_routines WHERE menu_parent IN (
					--level3
					SELECT routine FROM prumo_routines WHERE menu_parent IN (
						--level2
						SELECT routine FROM prumo_routines WHERE menu_parent IN (
							--level1
							SELECT routine FROM prumo_routines WHERE menu_parent IS NULL
						)
					)
				)
			)
		)

		UNION

		SELECT 
			routine,
			(SELECT menu_label FROM prumo_routines WHERE routine=(SELECT menu_parent FROM prumo_routines WHERE routine=(SELECT menu_parent FROM prumo_routines WHERE routine=m4.menu_parent))) as level1,
			(SELECT menu_label FROM prumo_routines WHERE routine=(SELECT menu_parent FROM prumo_routines WHERE routine=m4.menu_parent)) as level2,
			(SELECT menu_label FROM prumo_routines WHERE routine=m4.menu_parent) as level3,
			menu_label as level4,
			CAST('_' as TEXT) as level5,
			4 as nivel,
			type
		FROM prumo_routines m4
		WHERE m4.routine IN (
			--level4
			SELECT routine FROM prumo_routines WHERE menu_parent IN (
				--level3
				SELECT routine FROM prumo_routines WHERE menu_parent IN (
					--level2
					SELECT routine FROM prumo_routines WHERE menu_parent IN (
						--level1
						SELECT routine FROM prumo_routines WHERE menu_parent IS NULL
					)
				)
			)
		)

		UNION

		SELECT 
			routine,
			(SELECT menu_label FROM prumo_routines WHERE routine=(SELECT menu_parent FROM prumo_routines WHERE routine=m3.menu_parent)) as level1,
			(SELECT menu_label FROM prumo_routines WHERE routine=m3.menu_parent) as level2,
			menu_label as level3,
			CAST('_' as TEXT) as level4,
			CAST(NULL as TEXT) as level5,
			3 as nivel,
			type
		FROM prumo_routines m3
		WHERE m3.routine IN (
			--level3
			SELECT routine FROM prumo_routines WHERE menu_parent IN (
				--level2
				SELECT routine FROM prumo_routines WHERE menu_parent IN (
					--level1
					SELECT routine FROM prumo_routines WHERE menu_parent IS NULL
				)
			)
		)

		UNION

		SELECT 
			routine,
			(SELECT menu_label FROM prumo_routines WHERE routine=m2.menu_parent) as level1,
			menu_label as level2,
			CAST('_' as TEXT) as level3,
			CAST(NULL as TEXT) as level4,
			CAST(NULL as TEXT) as level5,
			2 as nivel,
			type
		FROM prumo_routines m2
		WHERE m2.routine IN (
			--level2
			SELECT routine FROM prumo_routines WHERE menu_parent IN (
				--level1
				SELECT routine FROM prumo_routines WHERE menu_parent IS NULL
			)
		)

		UNION

		SELECT 
			routine,
			menu_label as level1,
			CAST('_' as TEXT) as level2,
			CAST(NULL as TEXT) as level3,
			CAST(NULL as TEXT) as level4,
			CAST(NULL as TEXT) as level5,
			1 as nivel,
			type
		FROM prumo_routines m1
		WHERE m1.menu_parent IS NULL
	) a
	ORDER BY level1,level2,level3,level4,level5
) search;

