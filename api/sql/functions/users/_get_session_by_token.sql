/**

Удалить

*/

CREATE OR REPLACE FUNCTION "users"."get_session_by_token"(char)
    RETURNS TABLE("user_id" integer, "token" char, "ttl" integer) AS
$BODY$
DECLARE
    rec record;
BEGIN

    SELECT * INTO rec FROM "users"."session" WHERE "users"."session"."token" = $1;
    
    IF rec.id IS NOT NULL
    THEN
        "user_id" := rec."user_id";
        "token"   := rec."token";
        "ttl"     := rec."ttl";
        RETURN NEXT;
    END IF;

    RETURN;

END
$BODY$
LANGUAGE plpgsql VOLATILE 
COST 100
ROWS 1000;

