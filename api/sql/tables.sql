# ________________________________________________________________________________________________
# ___ SCHEMA "PUBLIC" ____________________________________________________________________________

CREATE TYPE crud AS ENUM ('NONE', 'READ', 'CREATE', 'UPDATE', 'DELETE');

CREATE TABLE "public"."user"
(
    "id" SERIAL NOT NULL PRIMARY KEY
);

CREATE TABLE "public"."service"
(
    "id"  SERIAL NOT NULL PRIMARY KEY,
    "key" VARCHAR(32) NOT NULL
);

CREATE TABLE "public"."user_service"
(
    "key"        CHAR(40) NOT NULL PRIMARY KEY,
    "user_id"    INTEGER NOT NULL,
    "service_id" INTEGER NOT NULL,
    FOREIGN KEY("user_id") REFERENCES "public"."user"("id") ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY("service_id") REFERENCES "public"."service"("id") ON UPDATE CASCADE ON DELETE CASCADE
);



# _________________________________________________________________________________________________
# ___ SCHEMA "SETTINGS" ___________________________________________________________________________

CREATE SCHEMA "settings";

CREATE TABLE "settings"."setting"
(
    "id"     SERIAL NOT NULL PRIMARY KEY,
    "key"    VARCHAR(255) NOT NULL,
    "value"  TEXT NOT NULL,
    "type"   VARCHAR(255) NOT NULL,
    "access" VARCHAR(255) NOT NULL,
    "ttl"    INTEGER DEFAULT NULL
);



# ________________________________________________________________________________________________
# ___ SCHEMA "APPS" ______________________________________________________________________________

CREATE SCHEMA "apps";

CREATE TABLE "apps"."app"
(
    "id"      SERIAL NOT NULL PRIMARY KEY,
    "name"    VARCHAR(255) NOT NULL,
    "secret"  CHAR(20) NOT NULL,
    "enabled" BOOLEAN NOT NULL DEFAULT FALSE,
    "trusted" BOOLEAN NOT NULL DEFAULT FALSE,
    "domains" VARCHAR(255)[] DEFAULT NULL,
    "ip"      INET[] DEFAULT NULL
);

CREATE TABLE "apps"."scope"
(
    "id"   SERIAL NOT NULL PRIMARY KEY,
    "name" VARCHAR(255) NOT NULL
);

CREATE TABLE "apps"."app_scope"
(
    "id" SERIAL NOT NULL PRIMARY KEY,
    "app_id" INTEGER NOT NULL,
    "scope_id" INTEGER NOT NULL,
    FOREIGN KEY("app_id") REFERENCES "apps"."app"("id") ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY("scope_id") REFERENCES "apps"."scope"("id") ON UPDATE CASCADE ON DELETE CASCADE
);



# ________________________________________________________________________________________________
# ___ SCHEMA "SERVICES" __________________________________________________________________________

CREATE SCHEMA "services";

/* <- это нужно переодически очищать!!! да и вообще, пересмотреть! может лучше no-sql? */
/*
CREATE TABLE "service"."oauth2"
(
    "id" serial NOT NULL PRIMARY KEY,
    "user_service_key" char(40) NOT NULL,
    "access_token" varchar(255) NOT NULL,
    "refresh_token" varchar(255) DEFAULT NULL,
    "created" timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    "expires_in" integer DEFAULT NULL,
    "server_response" TEXT DEFAULT NULL,
    FOREIGN KEY("user_service_key") REFERENCES "public"."user_service"("key") ON UPDATE CASCADE ON DELETE CASCADE
);
*/



# ________________________________________________________________________________________________
# ___ SCHEMA "USERS" _____________________________________________________________________________

CREATE SCHEMA "users";

/*
Свойства должны начинаться с символа (со знака подчеркивания нельзя).
Нельзя использовать свойство с именем "id"
*/
CREATE TABLE "users"."property"
(
    "id" serial NOT NULL PRIMARY KEY,
    "user_id" integer NOT NULL,
    "key" varchar(255) NOT NULL,
    "value" text NOT NULL,
    FOREIGN KEY("user_id") REFERENCES "public"."user"("id") ON UPDATE CASCADE ON DELETE CASCADE,
    UNIQUE ("user_id", "key")
);


/*

В NoSQL ???

*/
/*CREATE TABLE "user"."session"
(
    "id" serial NOT NULL PRIMARY KEY,
    "user_id" integer NOT NULL,
    "token" char(13) NOT NULL UNIQUE,
    "created" timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    "ttl" integer DEFAULT NULL,
    `marker` char(40) NOT NULL, #<-- тут что-то таки надо придумать 
    FOREIGN KEY("user_id") REFERENCES "public"."user"("id") ON UPDATE CASCADE ON DELETE CASCADE
);*/


/* ___________________ TEST DATA _____________________ */
INSERT INTO "public"."user" VALUES(1);
INSERT INTO "public"."service" VALUES(1, 'vk');
INSERT INTO "public"."service" VALUES(2, 'ok');
INSERT INTO "users"."property"("user_id", "key", "value") VALUES(1, 'email', 'jb@stoiss.net');
INSERT INTO "users"."property"("user_id", "key", "value") VALUES(1, 'name', 'Stoiss777');


INSERT INTO "settings"."setting"("key", "value", "type", "access") VALUES('services.vk.auth.type',         'oauth2', 'scalar', 'public');
INSERT INTO "settings"."setting"("key", "value", "type", "access") VALUES('services.vk.auth.clientId',     '4977645', 'scalar', 'private');
INSERT INTO "settings"."setting"("key", "value", "type", "access") VALUES('services.vk.auth.clientSecret', '7JIphf0QwDRV56hgGTPY', 'scalar', 'private');
INSERT INTO "settings"."setting"("key", "value", "type", "access") VALUES('services.vk.auth.redirectUri',  'http://auth.invitations.stoiss.net/callback/oauth2/vk', 'scalar', 'private');
INSERT INTO "settings"."setting"("key", "value", "type", "access") VALUES('services.vk.auth.codeUrl',      'https://oauth.vk.com/authorize', 'scalar', 'private');
INSERT INTO "settings"."setting"("key", "value", "type", "access") VALUES('services.vk.auth.tokenUrl',     'https://oauth.vk.com/access_token', 'scalar', 'private');
INSERT INTO "settings"."setting"("key", "value", "type", "access") VALUES('services.vk.auth.scope',        '["friends","status","email","audio"]', 'json', 'private');

INSERT INTO "settings"."setting"("key", "value", "type", "access") VALUES('services.ok.auth.type',         'oauth2', 'scalar', 'public');
INSERT INTO "settings"."setting"("key", "value", "type", "access") VALUES('services.ok.auth.clientId',     '1150033152', 'scalar', 'private');
INSERT INTO "settings"."setting"("key", "value", "type", "access") VALUES('services.ok.auth.clientSecret', '61301974CCCFB2ABE3707FFA', 'scalar', 'private');
INSERT INTO "settings"."setting"("key", "value", "type", "access") VALUES('services.ok.auth.redirectUri',  'http://auth.invitations.stoiss.net/callback/oauth2/ok', 'scalar', 'private');
INSERT INTO "settings"."setting"("key", "value", "type", "access") VALUES('services.ok.auth.codeUrl',      'http://www.odnoklassniki.ru/oauth/authorize', 'scalar', 'private');
INSERT INTO "settings"."setting"("key", "value", "type", "access") VALUES('services.ok.auth.tokenUrl',     'https://api.odnoklassniki.ru/oauth/token.do', 'scalar', 'private');
INSERT INTO "settings"."setting"("key", "value", "type", "access") VALUES('services.ok.auth.scope',        '["PHOTO_CONTENT","SET_STATUS"]', 'json', 'private');


INSERT INTO 
    "apps"."app"("name", "secret", "enabled", "trusted", "domains", "ip") 
VALUES
    ('Official web client', 'j30ijasdPas=pkq8RqQe', true, true, '{"invitations.stoiss.net"}', '{"192.168.7.0/24"}');

INSERT INTO "apps"."scope"("name") VALUES('invitations');
INSERT INTO "apps"."scope"("name") VALUES('friends');
INSERT INTO "apps"."app_scope"("app_id", "scope_id") VALUES(1, 1);
INSERT INTO "apps"."app_scope"("app_id", "scope_id") VALUES(1, 2);