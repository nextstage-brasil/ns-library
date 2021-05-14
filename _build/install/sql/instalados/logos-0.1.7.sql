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

-- professores que lecionam modulos no polo
drop view if exists myviews.montagrade_professores_disponiveis;

create or replace view myviews.montagrade_professores_disponiveis as
select 
	a.id_empresa,
	a.id_polo, a.nome_polo,
	curso.id_curso, curso.nome_curso,
	mo.id_modulo, mo.nome_modulo, (c.extra_linktable->>'sort')::integer as ordem, 
		(mo.extras_modulo->>'enc')::integer as qtde_encontros,
		mo.extras_modulo->>'reoc' as reocorrencia,
		mo.extras_modulo->>'dur' as  duracao_encontros,
		(mo.extras_modulo->>'ava')::integer as  qtde_avaliacoes,
	-- ultimo encontro
	(select max (dt_hr_grade_polo) from grade_polo where id_curso= curso.id_curso and id_polo= a.id_polo and id_modulo= mo.id_modulo) as ult_encontro,
	-- obter json dos professores ativos para o modulo
	(
      select array_to_json(array_agg(row_to_json(d)))
      from (
        select x.id_usuario as id, x.nome_usuario as nome, coalesce(d.extra_linktable->>'sort', '0')::integer as sort from app_usuario x
			inner join app_linktable d on d.id_lt_rel=4771 and d.id_left_linktable= c.id_right_linktable -- obtem os professores do modulo (MODULO|USUARIO)
			inner join app_linktable e on e.id_lt_rel=4773 and e.id_left_linktable= a.id_polo and e.id_right_linktable= d.id_right_linktable -- obtem os professores do modulo para este polo (POLO|USUARIO)
			where x.id_usuario = e.id_right_linktable       
      ) d
    ) as professores
from polo a
inner join app_linktable b on b.id_left_linktable= a.id_polo and b.id_lt_rel= 2 -- obtem os cursos do polo (POLO|CURSO)
inner join curso curso on curso.id_curso = b.id_right_linktable
inner join app_linktable c on c.id_lt_rel=1 and c.id_left_linktable= b.id_right_linktable -- obtem os modulos do curso (CURSO|MODULO)
inner join modulo mo on mo.id_modulo = c.id_right_linktable
;

ALTER TABLE public.matricula ADD mens_matricula decimal(12,2) NOT NULL -- Valor da mensalidade|Valor bruto, sem descontos;




