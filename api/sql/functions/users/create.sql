/**
 * Function 1: simply creates a new user
 *
 */
CREATE OR REPLACE FUNCTION "users"."create"()
    RETURNS integer AS
$BODY$
DECLARE
    uid integer;
BEGIN

    uid := nextval('public.user_id_seq');
    INSERT INTO "public"."user"(id) VALUES(uid);

    RETURN uid;

END
$BODY$
LANGUAGE plpgsql VOLATILE 
COST 100;



/**
 * Function 2: creating a new user with properties
 *
 */
CREATE OR REPLACE FUNCTION "users"."create"(json)
    RETURNS crud AS
$BODY$
DECLARE
    rec record;
    uid integer;
BEGIN

    uid := "users"."create"();

    FOR rec IN
        SELECT * FROM json_each_text($1) WHERE "key" <> 'id'
    LOOP
        INSERT INTO "users"."property"("user_id", "key", "value") VALUES(uid, rec."key", rec."value");
    END LOOP;

    RETURN 'CREATE';

END
$BODY$
LANGUAGE plpgsql VOLATILE 
COST 100;
