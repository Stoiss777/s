CREATE OR REPLACE FUNCTION "users"."get_properties_by_id"(integer)
    RETURNS TABLE ("key" varchar(255), "value" text) AS
$BODY$
DECLARE
    rec record;
BEGIN

    IF EXISTS(SELECT * FROM "public"."user" WHERE id = $1)
    THEN
        "key"   := 'id';
        "value" := $1;
        RETURN NEXT;
    ELSE
        RETURN;
    END IF;

    FOR rec IN 
        SELECT * FROM "users"."property" WHERE user_id = $1
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