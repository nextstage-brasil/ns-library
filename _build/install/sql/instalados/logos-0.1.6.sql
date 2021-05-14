rollback;
begin;


CREATE SEQUENCE public.app_uploadfile_id_seq;
ALTER TABLE public.app_uploadfile ALTER COLUMN id_uploadfile SET DEFAULT nextval('app_uploadfile_id_seq'::regclass);
ALTER TABLE public.app_uploadfile ALTER COLUMN st_uploadfile SET DEFAULT 'Local'::text;
ALTER TABLE public.app_uploadfile ADD CONSTRAINT app_uploadfile_un UNIQUE (filename_uploadfile,entidade_uploadfile,valorid_uploadfile);

-- 26/06/2020
DROP VIEW myviews.prof_polo_modulo;
DROP VIEW myviews.professores;
ALTER TABLE public.app_usuario ALTER COLUMN id_usuario TYPE int8 USING id_usuario::int8;
ALTER TABLE public.grade_polo RENAME COLUMN id_leciona TO id_usuario;
ALTER TABLE public.grade_polo ALTER COLUMN id_usuario TYPE int8 USING id_usuario::int8;
ALTER TABLE public.grade_polo DROP CONSTRAINT leciona_grade_polo_fk;
ALTER TABLE public.grade_polo ADD CONSTRAINT grade_polo_usuario_fk FOREIGN KEY (id_usuario) REFERENCES public.app_usuario(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE;

CREATE OR REPLACE VIEW myviews.professores
AS SELECT app_usuario.id_usuario,
    app_usuario.createtime_usuario,
    app_usuario.id_empresa,
    app_usuario.nome_usuario,
    app_usuario.tipo_usuario,
    app_usuario.perfil_usuario,
    app_usuario.email_usuario,
    app_usuario.senha_usuario,
    app_usuario.data_nascimento_usuario,
    app_usuario.sexo_usuario,
    app_usuario.rg_usuario,
    app_usuario.cpf_usuario,
    app_usuario.token_altera_senha_usuario,
    app_usuario.cep_usuario,
    app_usuario.complemento_cep_usuario,
    app_usuario.token_validade_usuario,
    app_usuario.ult_acesso_usuario,
    app_usuario.data_senha_usuario,
    app_usuario.session_time_usuario,
    app_usuario.avatar_usuario,
    app_usuario.is_alive_usuario,
    app_usuario.extras_usuario,
    app_usuario.link_public_usuario,
    app_usuario.status_usuario
   FROM app_usuario
  WHERE (app_usuario.extras_usuario ->> 'isProfessor'::text) = 'Sim'::text;







commit;