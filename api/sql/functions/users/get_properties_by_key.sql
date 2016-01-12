CREATE OR REPLACE FUNCTION "users"."get_properties_by_key"(char)
    RETURNS TABLE ("key" varchar(255), "value" text) AS
$BODY$
DECLARE
    rec record;
BEGIN

    IF EXISTS(SELECT * FROM "public"."user" WHERE id = (SELECT id FROM "public"."user_service" WHERE "public"."user_service"."key" = $1))
    THEN
        "key"   := 'id';
        "value" := $1;
        RETURN NEXT;
    ELSE
        RETURN;
    END IF;

    FOR rec IN 
        SELECT * FROM "users"."property" WHERE "user_id" = $1
    LOOP
		"key"   := rec."key";
        "value" := rec."value";
        RETURN NEXT;
    END LOOP;
    
    RETURN;

END
$BODY$
LANGUAGE plpgsql VOLATILE 
COST 100 
ROWS 1000;
