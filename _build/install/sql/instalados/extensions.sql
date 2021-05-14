CREATE OPERATOR ~@ (LEFTARG = jsonb, RIGHTARG = text, PROCEDURE = jsonb_exists);    
CREATE OPERATOR ~@| (LEFTARG = jsonb, RIGHTARG = text[], PROCEDURE = jsonb_exists_any);
CREATE OPERATOR ~@& (LEFTARG = jsonb, RIGHTARG = text[], PROCEDURE = jsonb_exists_all);

create extension if not exists unaccent;
create extension if not exists pg_trgm;
create extension if not exists fuzzystrmatch;
CREATE extension if not exists btree_gin;
CREATE EXTENSION if not exists pgcrypto;

CREATE OR REPLACE FUNCTION public.jsonb_minus(json jsonb, keys text[])
 RETURNS jsonb
 LANGUAGE sql
 IMMUTABLE STRICT
AS $function$
  SELECT
    -- Only executes opration if the JSON document has the keys
    CASE WHEN "json" ?| "keys"
      THEN COALESCE(
          (SELECT ('{' || string_agg(to_json("key")::text || ':' || "value", ',') || '}')
           FROM jsonb_each("json")
           WHERE "key" <> ALL ("keys")),
          '{}'
        )::jsonb
      ELSE "json"
    END
$function$
;

CREATE OR REPLACE FUNCTION public.jsonb_minus(arg1 jsonb, arg2 jsonb)
 RETURNS jsonb
 LANGUAGE sql
AS $function$

  SELECT
    COALESCE(
      json_object_agg(
        key,
        CASE
          -- if the value is an object and the value of the second argument is
          -- not null, we do a recursion
          WHEN jsonb_typeof(value) = 'object' AND arg2 -> key IS NOT NULL
          THEN jsonb_minus(value, arg2 -> key)
          -- for all the other types, we just return the value
          ELSE value
        END
      ),
    '{}'
    )::jsonb
  FROM
    jsonb_each(arg1)
  WHERE
    arg1 -> key <> arg2 -> key
    OR arg2 -> key IS NULL

$function$
;


CREATE OPERATOR - (
    PROCEDURE = jsonb_minus,
    LEFTARG   = jsonb,
    RIGHTARG  = jsonb );
