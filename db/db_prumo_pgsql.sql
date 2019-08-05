CREATE SCHEMA prumo;

CREATE TABLE prumo.config
(
  config_name character varying(40) NOT NULL,
  config_value character varying(255),
  CONSTRAINT config_pkey PRIMARY KEY (config_name)
)
WITH (
  OIDS=FALSE
);

CREATE TABLE prumo.syslogin
(
  username character varying(30) NOT NULL,
  fullname character varying(50) NOT NULL,
  "password" character varying(120),
  enabled boolean NOT NULL DEFAULT 't',
  CONSTRAINT syslogin_pkey PRIMARY KEY (username)
)
WITH (
  OIDS=FALSE
);

CREATE TABLE prumo.groups
(
  enabled boolean NOT NULL DEFAULT 't',
  groupname character varying(30) NOT NULL,
  CONSTRAINT groups_pkey PRIMARY KEY (groupname)
)
WITH (
  OIDS=FALSE
);

CREATE TABLE prumo.groups_syslogin
(
  groupname character varying(30) NOT NULL,
  username character varying(30) NOT NULL,
  CONSTRAINT groups_syslogin_pkey PRIMARY KEY (groupname, username),
  CONSTRAINT groups_syslogin_groupname_fkey FOREIGN KEY (groupname)
      REFERENCES prumo.groups (groupname) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT groups_syslogin_username_fkey FOREIGN KEY (username)
      REFERENCES prumo.syslogin (username) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
)
WITH (
  OIDS=FALSE
);

CREATE TABLE prumo.routines
(
  routine character varying(40) NOT NULL,
  enabled boolean NOT NULL DEFAULT 't',
  audit boolean NOT NULL DEFAULT 't',
  link character varying(255),
  description text,
  menu_parent character varying(40),
  menu_label character varying(40),
  menu_icon character varying(255),
  type character varying(40),
  CONSTRAINT routines_pkey PRIMARY KEY (routine),
  CONSTRAINT routines_menu_parent_fkey FOREIGN KEY (menu_parent)
      REFERENCES prumo.routines (routine) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
)
WITH (
  OIDS=FALSE
);

CREATE TABLE prumo.routines_groups
(
  routine character varying(40) NOT NULL,
  groupname character varying(40) NOT NULL,
  c boolean NOT NULL DEFAULT 'f',
  r boolean NOT NULL DEFAULT 'f',
  u boolean NOT NULL DEFAULT 'f',
  d boolean NOT NULL DEFAULT 'f',
  CONSTRAINT routines_groups_pkey PRIMARY KEY (routine, groupname),
  CONSTRAINT routines_groups_groupname_fkey FOREIGN KEY (groupname)
      REFERENCES prumo.groups (groupname) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT routines_groups_routine_fkey FOREIGN KEY (routine)
      REFERENCES prumo.routines (routine) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
)
WITH (
  OIDS=FALSE
);

CREATE TABLE prumo.acess_denied
(
  id serial NOT NULL,
  username character varying(30) NOT NULL,
  routine character varying(40) NOT NULL,
  permission character varying(10) NOT NULL,
  date_time timestamp without time zone NOT NULL DEFAULT now(),
  CONSTRAINT acess_denied_pkey PRIMARY KEY (id ),
  CONSTRAINT acess_denied_routine_fkey FOREIGN KEY (routine)
      REFERENCES prumo.routines (routine) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT acess_denied_username_fkey FOREIGN KEY (username)
      REFERENCES prumo.syslogin (username) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
)
WITH (
  OIDS=FALSE
);

CREATE TABLE prumo.log_sql
(
  log_serial serial NOT NULL,
  log_timestamp timestamp without time zone NOT NULL DEFAULT now(),
  log_obj_name character varying(100) NOT NULL,
  usr_login character varying(40),
  log_prumo_method character varying(40) NOT NULL,
  log_statement text NOT NULL,
  routine character varying(40),
  CONSTRAINT log_sql_pkey PRIMARY KEY (log_serial),
  CONSTRAINT log_sql_routine_fkey FOREIGN KEY (routine)
      REFERENCES prumo.routines (routine) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT log_sql_usr_login_fkey FOREIGN KEY (usr_login)
      REFERENCES prumo.syslogin (username) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
)
WITH (
  OIDS=FALSE
);

CREATE TABLE prumo.update_framework
(
   file_name character varying(100) NOT NULL, 
   usr_login character varying(40), 
   date_time timestamp without time zone NOT NULL DEFAULT now(), 
   PRIMARY KEY (file_name)
) 
WITH (
  OIDS = FALSE
);

CREATE TABLE prumo.reminder
(
	id serial NOT NULL,
	event character varying(30) NOT NULL,
	description text NOT NULL,
	reminder_date date NOT NULL,
	repeat_every integer,
	repeat_interval character varying(10),
	username character varying(30),
    last_seen date NOT NULL DEFAULT now()::date - '1 day'::interval,
	PRIMARY KEY (id),
	FOREIGN KEY (username) REFERENCES prumo.syslogin (username) MATCH SIMPLE ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE prumo.active_reminder
(
	id integer NOT NULL,
	show_at timestamp without time zone NOT NULL,
	PRIMARY KEY (id),
	FOREIGN KEY (id) REFERENCES prumo.reminder (id) MATCH SIMPLE ON UPDATE CASCADE ON DELETE CASCADE
);

INSERT INTO prumo.routines (routine,enabled,audit,type) VALUES ('prumo_reminders','t','f','view');

INSERT INTO prumo.routines (routine, enabled, type, menu_label, menu_icon) VALUES ('prumo_system', 't', 'root_menu', 'SISTEMA', 'prumo/images/icons/example.png');
INSERT INTO prumo.routines (routine, enabled, type, menu_parent, menu_label, menu_icon) VALUES ('prumo_access_control', 't', 'root_menu', 'prumo_system', 'Controle de Acesso', 'prumo/images/icons/example.png');

INSERT INTO prumo.routines (routine, link, enabled, type, menu_parent, menu_label, menu_icon) VALUES ('prumo_groups',         'prumo/view_groups.php',          't', 'view', 'prumo_access_control', 'Grupos de Usuários',   'prumo/images/icons/users.png');
INSERT INTO prumo.routines (routine, link, enabled, type, menu_parent, menu_label, menu_icon) VALUES ('prumo_controlPanel',   'prumo/view_control_panel.php',   't', 'view', 'prumo_system',         'Painel de Controle',   'prumo/images/icons/system.png');
INSERT INTO prumo.routines (routine, link, enabled, type, menu_parent, menu_label, menu_icon) VALUES ('prumo_changePassword', 'prumo/view_change_password.php', 't', 'view', 'prumo_system',         'Alterar Senha',        'prumo/images/icons/key.png');
INSERT INTO prumo.routines (routine, link, enabled, type, menu_parent, menu_label, menu_icon) VALUES ('prumo_about',          'prumo/view_about.php',           't', 'view', 'prumo_system',         'Sobre o Prumo',        'prumo/images/icons/exclamation.png');
INSERT INTO prumo.routines (routine, link, enabled, type, menu_parent, menu_label, menu_icon) VALUES ('prumo_users',          'prumo/view_users.php',           't', 'view', 'prumo_access_control', 'Usuários',             'prumo/images/icons/users.png');
INSERT INTO prumo.routines (routine, link, enabled, type, menu_parent, menu_label, menu_icon) VALUES ('prumo_routines',       'prumo/view_routines.php',        't', 'view', 'prumo_access_control', 'Rotinas e permissões', 'prumo/images/icons/users.png');
INSERT INTO prumo.routines (routine, link, enabled, type, menu_parent, menu_label, menu_icon) VALUES ('prumo_submission',     'prumo/view_submission.php',      't', 'view', 'prumo_system',         'Instruções iniciais',  'prumo/images/icons/exclamation.png');
INSERT INTO prumo.routines (routine, link, enabled, type, menu_parent, menu_label, menu_icon) VALUES ('prumo_update',         'prumo/view_update.php',          't', 'view', 'prumo_system',         'Atualização',          'prumo/images/icons/system.png');
INSERT INTO prumo.routines (routine, link, enabled, type, menu_parent, menu_label, menu_icon) VALUES ('prumo_devtools',       'prumo/ctrl_devtools.php',        't', 'view', 'prumo_system',         'Desenvolvimento',      'prumo/images/icons/system.png');

INSERT INTO prumo.syslogin(username, fullname, "password") VALUES ('admin', 'Administrador do sistema', '$argon2id$v=19$m=65536,t=2,p=1$Rul0StMsJYFfy/LJuWn8aw$crsoRO623LuQFT7ZcgRcLrFWJbMT/E27eNzl8yVp33A');

INSERT INTO prumo.groups(enabled, groupname) VALUES ('t', 'sysadmin');
INSERT INTO prumo.groups(enabled, groupname) VALUES ('t', 'dev');
INSERT INTO prumo.groups_syslogin(groupname, username) VALUES ('sysadmin', 'admin');
INSERT INTO prumo.groups_syslogin(groupname, username) VALUES ('dev', 'admin');

INSERT INTO prumo.routines_groups(routine, groupname, c, r, u, d) VALUES ('prumo_groups', 'sysadmin', 't', 't', 't', 't');
INSERT INTO prumo.routines_groups(routine, groupname, c, r, u, d) VALUES ('prumo_controlPanel', 'sysadmin', 't', 't', 't', 't');
INSERT INTO prumo.routines_groups(routine, groupname, c, r, u, d) VALUES ('prumo_changePassword', 'sysadmin', 't', 't', 't', 't');
INSERT INTO prumo.routines_groups(routine, groupname, c, r, u, d) VALUES ('prumo_about', 'sysadmin', 't', 't', 't', 't');
INSERT INTO prumo.routines_groups(routine, groupname, c, r, u, d) VALUES ('prumo_system', 'sysadmin', 't', 't', 't', 't');
INSERT INTO prumo.routines_groups(routine, groupname, c, r, u, d) VALUES ('prumo_users', 'sysadmin', 't', 't', 't', 't');
INSERT INTO prumo.routines_groups(routine, groupname, c, r, u, d) VALUES ('prumo_routines', 'sysadmin', 't', 't', 't', 't');
INSERT INTO prumo.routines_groups(routine, groupname, c, r, u, d) VALUES ('prumo_submission', 'sysadmin', 't', 't', 't', 't');
INSERT INTO prumo.routines_groups(routine, groupname, c, r, u, d) VALUES ('prumo_access_control', 'sysadmin', 't', 't', 't', 't');
INSERT INTO prumo.routines_groups(routine, groupname, c, r, u, d) VALUES ('prumo_update', 'sysadmin', 't', 't', 't', 't');
INSERT INTO prumo.routines_groups(routine, groupname, c, r, u, d) VALUES ('prumo_devtools', 'dev', 't', 't', 't', 't');

INSERT INTO prumo.groups(enabled, groupname) VALUES ('t', 'change_password');
INSERT INTO prumo.routines_groups(routine, groupname, c, r, u, d) VALUES ('prumo_system', 'change_password', 'f', 't', 'f', 'f');
INSERT INTO prumo.routines_groups(routine, groupname, c, r, u, d) VALUES ('prumo_changePassword', 'change_password', 'f', 't', 't', 'f');

UPDATE prumo.routines SET routine='prumo_reminders',link='prumo/view_reminder.php',description=NULL,menu_parent='prumo_system',menu_label='Lembretes',menu_icon='prumo/images/icons/reminder.png' WHERE routine='prumo_reminders';
INSERT INTO prumo.routines_groups (routine,groupname,c,r,u,d) VALUES ('prumo_reminders','sysadmin','t','t','t','t');

CREATE VIEW prumo.v_menu AS
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
			(SELECT menu_label FROM prumo.routines WHERE routine=(SELECT menu_parent FROM prumo.routines WHERE routine=(SELECT menu_parent FROM prumo.routines WHERE routine=(SELECT menu_parent FROM prumo.routines WHERE routine=m5.menu_parent)))) as level1,
			(SELECT menu_label FROM prumo.routines WHERE routine=(SELECT menu_parent FROM prumo.routines WHERE routine=(SELECT menu_parent FROM prumo.routines WHERE routine=m5.menu_parent))) as level2,
			(SELECT menu_label FROM prumo.routines WHERE routine=(SELECT menu_parent FROM prumo.routines WHERE routine=m5.menu_parent)) as level3,
			(SELECT menu_label FROM prumo.routines WHERE routine=m5.menu_parent) as level4,
			menu_label as level5,
			5 as nivel,
			type
		FROM prumo.routines m5
		WHERE m5.routine IN (
			--level5
			SELECT routine FROM prumo.routines WHERE menu_parent IN (
				--level4
				SELECT routine FROM prumo.routines WHERE menu_parent IN (
					--level3
					SELECT routine FROM prumo.routines WHERE menu_parent IN (
						--level2
						SELECT routine FROM prumo.routines WHERE menu_parent IN (
							--level1
							SELECT routine FROM prumo.routines WHERE menu_parent IS NULL
						)
					)
				)
			)
		)

		UNION

		SELECT 
			routine,
			(SELECT menu_label FROM prumo.routines WHERE routine=(SELECT menu_parent FROM prumo.routines WHERE routine=(SELECT menu_parent FROM prumo.routines WHERE routine=m4.menu_parent))) as level1,
			(SELECT menu_label FROM prumo.routines WHERE routine=(SELECT menu_parent FROM prumo.routines WHERE routine=m4.menu_parent)) as level2,
			(SELECT menu_label FROM prumo.routines WHERE routine=m4.menu_parent) as level3,
			menu_label as level4,
			CAST('_' as TEXT) as level5,
			4 as nivel,
			type
		FROM prumo.routines m4
		WHERE m4.routine IN (
			--level4
			SELECT routine FROM prumo.routines WHERE menu_parent IN (
				--level3
				SELECT routine FROM prumo.routines WHERE menu_parent IN (
					--level2
					SELECT routine FROM prumo.routines WHERE menu_parent IN (
						--level1
						SELECT routine FROM prumo.routines WHERE menu_parent IS NULL
					)
				)
			)
		)

		UNION

		SELECT 
			routine,
			(SELECT menu_label FROM prumo.routines WHERE routine=(SELECT menu_parent FROM prumo.routines WHERE routine=m3.menu_parent)) as level1,
			(SELECT menu_label FROM prumo.routines WHERE routine=m3.menu_parent) as level2,
			menu_label as level3,
			CAST('_' as TEXT) as level4,
			CAST(NULL as TEXT) as level5,
			3 as nivel,
			type
		FROM prumo.routines m3
		WHERE m3.routine IN (
			--level3
			SELECT routine FROM prumo.routines WHERE menu_parent IN (
				--level2
				SELECT routine FROM prumo.routines WHERE menu_parent IN (
					--level1
					SELECT routine FROM prumo.routines WHERE menu_parent IS NULL
				)
			)
		)

		UNION

		SELECT 
			routine,
			(SELECT menu_label FROM prumo.routines WHERE routine=m2.menu_parent) as level1,
			menu_label as level2,
			CAST('_' as TEXT) as level3,
			CAST(NULL as TEXT) as level4,
			CAST(NULL as TEXT) as level5,
			2 as nivel,
			type
		FROM prumo.routines m2
		WHERE m2.routine IN (
			--level2
			SELECT routine FROM prumo.routines WHERE menu_parent IN (
				--level1
				SELECT routine FROM prumo.routines WHERE menu_parent IS NULL
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
		FROM prumo.routines m1
		WHERE m1.menu_parent IS NULL
	) a
	ORDER BY level1,level2,level3,level4,level5
) search;

