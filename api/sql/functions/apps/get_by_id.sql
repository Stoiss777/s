CREATE OR REPLACE FUNCTION "apps"."get_by_id"(integer)
    RETURNS TABLE ("name" varchar, "secret" varchar, enabled boolean, trusted boolean, domains varchar[], ip inet[], scopes varchar[]) AS
$BODY$
DECLARE
    rec record;
BEGIN

    FOR rec IN 

        SELECT 
            a.*, array_agg(s."name") AS "scopes"
        FROM 
            "apps"."app" a
        LEFT JOIN
            "apps"."app_scope" r ON a."id" = r."app_id"
        LEFT JOIN
            "apps"."scope" s ON s."id" = r."scope_id"
        WHERE
            a."id" = $1
        GROUP BY
            a."id", a."name", a."enabled", a."secret", a."domains", a."ip"

    LOOP

        "name"    := "rec"."name";
        "secret"  := "rec"."secret";
        "enabled" := "rec"."enabled";
        "trusted" := "rec"."trusted";
        "domains" := "rec"."domains";
        "ip"      := "rec"."ip";
        IF (array_length("rec"."scopes", 1) = 1) AND (rec.scopes[0] IS NULL) THEN
            "scopes" := NULL;
        ELSE
            "scopes" := "rec"."scopes";
        END IF;
        RETURN NEXT;

    END LOOP;

    RETURN;

END
$BODY$
LANGUAGE plpgsql VOLATILE 
COST 100 
ROWS 1000;
