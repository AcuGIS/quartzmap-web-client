CREATE TYPE public.userlevel AS ENUM ('Admin', 'User');

CREATE TABLE public.user (	id SERIAL PRIMARY KEY,
    name character varying(250) NOT NULL,
    email character varying(250) NOT NULL,
    password character varying(255) NOT NULL,
		ftp_user character varying(250),
		pg_password character varying(250),
    accesslevel public.userlevel DEFAULT 'User',
		owner_id integer NOT NULL	REFERENCES public.user(id),
		UNIQUE(email)
);

CREATE TABLE public.access_groups (	id SERIAL PRIMARY KEY,
	name character varying(255) NOT NULL,
	owner_id integer NOT NULL	REFERENCES public.user(id)
);

CREATE TABLE public.user_access (	id SERIAL PRIMARY KEY,
    user_id integer NOT NULL					REFERENCES public.user(id),
    access_group_id integer NOT NULL	REFERENCES public.access_groups(id),
		UNIQUE(user_id, access_group_id)
);

CREATE TABLE public.pglink (	id SERIAL PRIMARY KEY,
		name character varying(250) NOT NULL,
		host character varying(250) NOT NULL,
		port integer NOT NULL default 5432,
		username character varying(250) NOT NULL,
    password character varying(250) NOT NULL,
		dbname character varying(80) NOT NULL,
		svc_name character varying(50) NOT NULL,
		owner_id 	integer NOT NULL	REFERENCES public.user(id)
);

CREATE TABLE public.gslink (	id SERIAL PRIMARY KEY,
		name character varying(250) NOT NULL,
		url character varying(250) NOT NULL,
		username character varying(250),
    password character varying(250),
		owner_id 	integer NOT NULL	REFERENCES public.user(id)
);

CREATE TABLE public.map ( id SERIAL PRIMARY KEY,
    name character varying(50) NOT NULL,
		description character varying(50) NOT NULL,
		owner_id integer NOT NULL	REFERENCES public.user(id)
);

CREATE TABLE public.map_pglink ( id SERIAL PRIMARY KEY,
    map_id 		integer NOT NULL REFERENCES public.map(id),
    pglink_id integer NOT NULL REFERENCES public.pglink(id),
		UNIQUE(map_id, pglink_id)
);

CREATE TABLE public.map_gslink ( id SERIAL PRIMARY KEY,
    map_id 		integer NOT NULL REFERENCES public.map(id),
    gslink_id integer NOT NULL REFERENCES public.gslink(id),
		UNIQUE(map_id, gslink_id)
);

CREATE TABLE public.map_access ( id SERIAL PRIMARY KEY,
    map_id 						integer NOT NULL REFERENCES public.map(id),
    access_group_id 	integer NOT NULL REFERENCES public.access_groups(id),
		UNIQUE(map_id, access_group_id)
);

CREATE TABLE public.permalink (	id SERIAL PRIMARY KEY,
		description character varying(255),
		page character varying(255),
		query character varying(255),
    map_id integer NOT NULL		REFERENCES public.map(id),
		created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		expires TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP + interval '1 hour',
		visits			 integer NOT NULL DEFAULT 0,
		visits_limit integer NOT NULL DEFAULT 1,
		hash character varying(36) NOT NULL,
		owner_id integer NOT NULL	REFERENCES public.user(id)
);

CREATE TABLE public.signup ( id SERIAL PRIMARY KEY,
    name character varying(250) NOT NULL,
    email character varying(250) NOT NULL,
    password character varying(250) NOT NULL,
    verify character varying(250) NOT NULL,
		UNIQUE(email)
);
