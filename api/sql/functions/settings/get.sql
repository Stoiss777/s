CREATE OR REPLACE FUNCTION "settings"."get"(varchar)
    RETURNS TABLE ("key" varchar, "value" text, "type" varchar, "access" varchar, "ttl" integer) AS
$BODY$
DECLARE
    rec record;
    keys varchar[];
    res varchar[];
    i integer;
    j integer := 0;
BEGIN

    keys := regexp_split_to_array(replace(replace($1, '_', '\_'), '%', '\%'), E'\\.');

    FOR i IN 1..array_upper(keys, 1) LOOP
        IF keys[i] = '*'
        THEN
            res[j] := '%';
            EXIT;
        END IF;
        res[j] := keys[i];
        j := j + 1;
    END LOOP;

    FOR rec IN 
        SELECT * FROM "settings"."setting" WHERE "settings"."setting"."key" LIKE array_to_string(res, '.')
    LOOP
		"key"    := rec."key";
        "value"  := rec."value";
        "type"   := rec."type";
        "access" := rec."access";
        "ttl"    := rec."ttl";
        RETURN NEXT;
    END LOOP;
    
    RETURN;

END
$BODY$
LANGUAGE plpgsql VOLATILE 
COST 100 
ROWS 1000;
