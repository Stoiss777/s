CREATE OR REPLACE FUNCTION "users"."get_id_by_properties"(json)
    RETURNS TABLE("user_id" integer) AS
$BODY$
DECLARE
    i record;
    j record;
    r integer[];
BEGIN

    /*

    INPUT JSON:
    [{"email": "sample1@sample.com", "name": "Sample1"},{"email": "sample2@sample.com", "name": "Sample2"}]

    */

    FOR i IN

        SELECT * FROM json_array_elements($1)

    LOOP

        FOR j IN    


            SELECT
                p.user_id
            FROM 
                json_each_text(i."value") AS t
            INNER JOIN
                "users"."property" AS p ON t."key" = p."key" AND t."value" = p."value"

        LOOP

            r := r || j.user_id;

        END LOOP;

    END LOOP;


    -------------------------------------------------------

    FOR i IN

        SELECT DISTINCT(unnest) FROM unnest(r)

    LOOP

        "user_id" := i."unnest";
        RETURN NEXT;

    END LOOP;


    RETURN;

END
$BODY$
LANGUAGE plpgsql VOLATILE 
COST 100
ROWS 1000;