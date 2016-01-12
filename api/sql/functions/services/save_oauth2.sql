/**

$1 - key,
$2 - access_token,
$3 - expires_in,
$4 - refresh_token,
$5 - server_response

*/
CREATE OR REPLACE FUNCTION "service"."save_oauth2"(char, varchar, integer, varchar, text)
    RETURNS crud AS
$BODY$
BEGIN

    IF EXISTS(SELECT * FROM "service"."oauth2" WHERE "user_service_key" = $1)
    THEN

        UPDATE 
            "service"."oauth2"
        SET
            "access_token" = $2,
            "expires_in" = $3,
            "refresh_token" = $4,
            "server_response" = $5,
            "created" = CURRENT_TIMESTAMP
        WHERE
            "user_service_key" = $1;
        
        RETURN 'UPDATE';

    ELSE

        INSERT INTO
            "service"."oauth2"("user_service_key", "access_token", "expires_in", "refresh_token", "server_response")
        VALUES
            ($1, $2, $3, $4, $5);

        RETURN 'CREATE';

    END IF;

END
$BODY$
LANGUAGE plpgsql VOLATILE 
COST 100;
