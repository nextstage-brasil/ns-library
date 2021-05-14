DROP FUNCTION IF EXISTS public.jsonb_minus(jsonb,_text);

DROP FUNCTION IF EXISTS public.jsonb_minus(jsonb,jsonb);

CREATE OR REPLACE FUNCTION public.jsonb_minus("left" jsonb, "right" jsonb)
 RETURNS jsonb
 LANGUAGE sql
 IMMUTABLE STRICT
AS $function$
  SELECT
    COALESCE(json_object_agg(
      "key",
      CASE
        -- if the value is an object and the value of the second argument is
        -- not null, we do a recursion
        WHEN jsonb_typeof("value") = 'object' AND "right" -> "key" IS NOT NULL
        THEN jsonb_minus("value", "right" -> "key")
        -- for all the other types, we just return the value
        ELSE "value"
      END
    ), '{}')::JSONB
  FROM
    jsonb_each("left")
  WHERE
    "left" -> "key" <> "right" -> "key"
    OR "right" -> "key" IS NULL
$function$
;

CREATE OR REPLACE FUNCTION public.jsonb_minus("left" jsonb, keys text[])
 RETURNS jsonb
 LANGUAGE sql
 IMMUTABLE STRICT
AS $function$
  SELECT
    CASE
      WHEN "left" ?| "keys"
        THEN COALESCE(
          (SELECT ('{' ||
                    string_agg(to_json("key")::TEXT || ':' || "value", ',') ||
                    '}')
             FROM jsonb_each("left")
            WHERE "key" <> ALL ("keys")),
          '{}'
        )::JSONB
      ELSE "left"
    END
$function$
;



-- Install audit schema
/*
drop schema if exists audit cascade; 
*/
create schema if not exists audit;

CREATE table if not exists audit.log (
	id bigserial NOT NULL,
	schema_name text NOT NULL,
	table_name text NOT NULL,
	relid oid NOT NULL,
	session_user_name text NOT NULL,
	current_user_name text NOT NULL,
	action_tstamp_tx timestamptz NOT NULL,
	action_tstamp_stm timestamptz NOT NULL,
	action_tstamp_clk timestamptz NOT NULL,
	transaction_id int8 NOT NULL,
	username text NULL,
	userid text NULL,
	client_addr inet NULL,
	client_port int4 NULL,
	client_query text NULL,
	"action" text NOT NULL,
	row_data jsonb NULL,
	changed_fields jsonb NULL,
	statement_only bool NOT NULL,
	CONSTRAINT log_action_check CHECK ((action = ANY (ARRAY['I'::text, 'D'::text, 'U'::text, 'T'::text]))),
	CONSTRAINT log_pkey PRIMARY KEY (id)
);
CREATE INDEX log_row_data_idx ON audit.log USING gin (row_data);
CREATE INDEX log_table_action_idx ON audit.log USING btree (table_name, action);



CREATE OR REPLACE FUNCTION audit.audit_table(target_table regclass, audit_rows boolean, audit_query_text boolean, ignored_cols text[])
 RETURNS void
 LANGUAGE plpgsql
AS $function$
DECLARE
  stm_targets TEXT = 'INSERT OR UPDATE OR DELETE OR TRUNCATE';
  _q_txt TEXT;
  _ignored_cols_snip TEXT = '';
BEGIN
  EXECUTE 'DROP TRIGGER IF EXISTS audit_trigger_row ON ' || target_table::TEXT;
  EXECUTE 'DROP TRIGGER IF EXISTS audit_trigger_stm ON ' || target_table::TEXT;

  IF audit_rows THEN
    IF array_length(ignored_cols,1) > 0 THEN
        _ignored_cols_snip = ', ' || quote_literal(ignored_cols);
    END IF;
    _q_txt = 'CREATE TRIGGER audit_trigger_row '
             'AFTER INSERT OR UPDATE OR DELETE ON ' ||
             target_table::TEXT ||
             ' FOR EACH ROW EXECUTE PROCEDURE audit.if_modified_func(' ||
             quote_literal(audit_query_text) ||
             _ignored_cols_snip ||
             ');';
    RAISE NOTICE '%', _q_txt;
    EXECUTE _q_txt;
    stm_targets = 'TRUNCATE';
  END IF;

  _q_txt = 'CREATE TRIGGER audit_trigger_stm AFTER ' || stm_targets || ' ON ' ||
           target_table ||
           ' FOR EACH STATEMENT EXECUTE PROCEDURE audit.if_modified_func('||
           quote_literal(audit_query_text) || ');';
  RAISE NOTICE '%', _q_txt;
  EXECUTE _q_txt;
END;
$function$
;

CREATE OR REPLACE FUNCTION audit.audit_table(target_table regclass, audit_rows boolean, audit_query_text boolean)
 RETURNS void
 LANGUAGE sql
AS $function$
  SELECT audit.audit_table($1, $2, $3, ARRAY[]::TEXT[]);
$function$
;


CREATE OR REPLACE FUNCTION audit.audit_table(target_table regclass)
 RETURNS void
 LANGUAGE sql
AS $function$
  SELECT audit.audit_table($1, BOOLEAN 't', BOOLEAN 't');
$function$
;


CREATE OR REPLACE FUNCTION audit.if_modified_func()
 RETURNS trigger
 LANGUAGE plpgsql
 SECURITY DEFINER
 SET search_path TO 'pg_catalog', 'public'
AS $function$
DECLARE
    audit_row audit.log;
    include_values BOOLEAN;
    log_diffs BOOLEAN;
    h_old JSONB;
    h_new JSONB;
    excluded_cols TEXT[] = ARRAY[]::TEXT[];
BEGIN
  IF TG_WHEN <> 'AFTER' THEN
    RAISE EXCEPTION 'audit.if_modified_func() may only run as an AFTER trigger';
  END IF;

  audit_row = ROW(
    nextval('audit.log_id_seq'),                    -- id
    TG_TABLE_SCHEMA::TEXT,                          -- schema_name
    TG_TABLE_NAME::TEXT,                            -- table_name
    TG_RELID,                                       -- relation OID for faster searches
    session_user::TEXT,                             -- session_user_name
    current_user::TEXT,                             -- current_user_name
    current_timestamp,                              -- action_tstamp_tx
    statement_timestamp(),                          -- action_tstamp_stm
    clock_timestamp(),                              -- action_tstamp_clk
    txid_current(),                                 -- transaction ID
    current_setting('audit.username'),              -- client application
    current_setting('audit.userid'),                -- client user name
    inet_client_addr(),                             -- client_addr
    inet_client_port(),                             -- client_port
    current_query(),                                -- top-level query or queries
    substring(TG_OP, 1, 1),                         -- action
    NULL,                                           -- row_data
    NULL,                                           -- changed_fields
    'f'                                             -- statement_only
    );

  IF NOT TG_ARGV[0]::BOOLEAN IS DISTINCT FROM 'f'::BOOLEAN THEN
    audit_row.client_query = NULL;
  END IF;

  IF TG_ARGV[1] IS NOT NULL THEN
    excluded_cols = TG_ARGV[1]::TEXT[];
  END IF;

  IF (TG_OP = 'INSERT' AND TG_LEVEL = 'ROW') THEN
    audit_row.changed_fields = to_jsonb(NEW.*) - excluded_cols;
  ELSIF (TG_OP = 'UPDATE' AND TG_LEVEL = 'ROW') THEN
    audit_row.row_data = to_jsonb(OLD.*) - excluded_cols;
    audit_row.changed_fields =
      (to_jsonb(NEW.*) - audit_row.row_data) - excluded_cols;
    IF audit_row.changed_fields = '{}'::JSONB THEN
      -- All changed fields are ignored. Skip this update.
      RETURN NULL;
    END IF;
  ELSIF (TG_OP = 'DELETE' AND TG_LEVEL = 'ROW') THEN
    audit_row.row_data = to_jsonb(OLD.*) - excluded_cols;
  ELSIF (TG_LEVEL = 'STATEMENT' AND
         TG_OP IN ('INSERT','UPDATE','DELETE','TRUNCATE')) THEN
    audit_row.statement_only = 't';
  ELSE
    RAISE EXCEPTION '[audit.if_modified_func] - Trigger func added as trigger '
                    'for unhandled case: %, %', TG_OP, TG_LEVEL;
    RETURN NULL;
  END IF;
  INSERT INTO audit.log VALUES (audit_row.*);
  RETURN NULL;
END;
$function$
;
