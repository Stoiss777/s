CREATE OR REPLACE FUNCTION "users"."bind_service"(integer, integer, char)
    RETURNS crud AS
$BODY$
BEGIN

    /* $1 - user_id, $2 - service_id, $3 - key */

    IF NOT EXISTS(SELECT * FROM "public"."user_service" WHERE "key" = $3) 
    THEN
        INSERT INTO "public"."user_service"("user_id", "service_id", "key") VALUES($1, $2, $3);
        RETURN 'CREATE';
    END IF;

    RETURN 'NONE';

END
$BODY$
LANGUAGE plpgsql VOLATILE 
COST 100;
