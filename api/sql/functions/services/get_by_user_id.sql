CREATE OR REPLACE FUNCTION "services"."get_by_user_id"(integer)
    RETURNS TABLE ("id" integer, "key" varchar) AS
$BODY$
DECLARE
    rec record;
BEGIN


    FOR rec IN    

        SELECT * FROM "public"."service" WHERE "public"."service"."id" IN
        (
            SELECT
                service_id
            FROM
                "public"."user_service"
            WHERE
                "public"."user_service"."user_id" = $1
        )


    LOOP

        "id"  := rec."id";
        "key" := rec."key";
        RETURN NEXT;

    END LOOP;

    RETURN;

END
$BODY$
LANGUAGE plpgsql VOLATILE 
COST 100
ROWS 1000;
