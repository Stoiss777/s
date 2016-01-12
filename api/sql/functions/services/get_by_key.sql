CREATE OR REPLACE FUNCTION "services"."get_by_key"(char)
    RETURNS TABLE ("id" integer, "key" varchar) AS
$BODY$
DECLARE
    rec record;
BEGIN

    SELECT * INTO rec FROM "public"."service" WHERE "public"."service"."key" = $1;

    IF rec.id IS NOT NULL
    THEN
        "id"  := rec."id";
        "key" := rec."key";
        RETURN NEXT;
    END IF;

    RETURN;

END
$BODY$
LANGUAGE plpgsql VOLATILE 
COST 100 
ROWS 1000;

