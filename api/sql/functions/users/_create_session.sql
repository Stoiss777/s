/*

удалить!!!

$1 - user_id
$2 - token
$3 - ttl

*/
CREATE OR REPLACE FUNCTION "users"."create_session"(integer, char, integer)
    RETURNS crud AS
$BODY$
BEGIN

    INSERT INTO "users"."session"("user_id", "token", "ttl") VALUES($1, $2, $3);

    RETURN 'CREATE';

END
$BODY$
LANGUAGE plpgsql VOLATILE 
COST 100;

