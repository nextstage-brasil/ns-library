<?php
/**

-- postgres
CREATE SEQUENCE public.endereco_id_endereco_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;

-- Table: public.endereco

-- DROP TABLE public.endereco;

CREATE TABLE public.endereco
(
  id_endereco integer NOT NULL DEFAULT nextval('endereco_id_endereco_seq'::regclass),
  createtime_endereco timestamp without time zone DEFAULT now(),
  nome_endereco character varying(100), -- Titulo do endereço
  entidade_endereco character varying(100) NOT NULL, -- Entidade relacionada
  valorid_endereco integer NOT NULL, -- ID Relacional
  id_municipio integer NOT NULL, -- Municipio
  cep_endereco character varying(8), -- CEP do endereço
  rua_endereco character varying(200) DEFAULT 'NÃO INFORMADO'::character varying, -- Rua, avenida...
  numero_endereco character varying(10), -- Número
  complemento_endereco character varying(50), -- Complemento
  bairro_endereco character varying(150), -- Bairro
  status_endereco character varying(50) NOT NULL DEFAULT 'INFORMADO'::character varying, -- Status: CONFIRMADO OU INFORMADO
  latitude_endereco character varying(20),
  longitude_endereco character varying(20),
  ponto_referencia_endereco character varying(250),
  CONSTRAINT endereco_fk PRIMARY KEY (id_endereco),
  CONSTRAINT municipio_endereco_fk FOREIGN KEY (id_municipio)
      REFERENCES public.municipio (id_municipio) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
)
WITH (
  OIDS=FALSE
);
ALTER TABLE public.endereco
  OWNER TO postgres;
COMMENT ON TABLE public.endereco
  IS 'Registro de endereços da pessoa';
COMMENT ON COLUMN public.endereco.nome_endereco IS 'Titulo do endereço';
COMMENT ON COLUMN public.endereco.entidade_endereco IS 'Entidade relacionada';
COMMENT ON COLUMN public.endereco.valorid_endereco IS 'ID Relacional';
COMMENT ON COLUMN public.endereco.id_municipio IS 'Municipio';
COMMENT ON COLUMN public.endereco.cep_endereco IS 'CEP do endereço';
COMMENT ON COLUMN public.endereco.rua_endereco IS 'Rua, avenida...';
COMMENT ON COLUMN public.endereco.numero_endereco IS 'Número';
COMMENT ON COLUMN public.endereco.complemento_endereco IS 'Complemento';
COMMENT ON COLUMN public.endereco.bairro_endereco IS 'Bairro';
COMMENT ON COLUMN public.endereco.status_endereco IS 'Status: CONFIRMADO OU INFORMADO';


-- Index: public.endereco_entidade_endereco_idx

-- DROP INDEX public.endereco_entidade_endereco_idx;

CREATE INDEX endereco_entidade_endereco_idx
  ON public.endereco
  USING btree
  (entidade_endereco COLLATE pg_catalog."default", valorid_endereco);







 * 
 * 
 */
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

