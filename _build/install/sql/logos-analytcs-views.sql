-- visao: financeiro
create or replace view myviews.analytcs_financeiro as 
select 
	b.id_matricula,
	g.nome_curso,  
	d.cpf_usuario::text as cpf, 
	d.nome_usuario as aluno,  
	c.nome_auxiliar as tipo,
	b.is_inscricao_matricula as eh_inscricao, 
	id_financeiro, 
	a.createtime_financeiro as data_lancamento, 
	vencimento_financeiro,
	a.pagamento_financeiro  as data_pagamento,  
	valor_financeiro/100 as valor , 
	(select extras_auxiliar->>'obs'::text from app_auxiliar where extras_auxiliar @> ('{"idUsuario":'||b.id_usuario||'}')::jsonb limit 1 ) as obs_inscricao, 
	b.obs_matricula,  
	nome_polo, 
	b.extras_matricula->>'confirmDate' as data_inicio, 
	f.nome_status as status_financeiro
from 
	financeiro a 
inner join matricula b on a.id_matricula  = b.id_matricula 
inner join app_auxiliar c on c.id_auxiliar = a.id_auxiliar 
inner join app_usuario d on d.id_usuario = b.id_usuario 
inner join polo e on e.id_polo = b.id_polo
inner join app_status f on f.id_status = a.id_status_financeiro
inner join curso g on g.id_curso  = b.id_curso;


-- visao: aluno em encontros
create or replace view myviews.analytcs_aluno_encontro as
select
	c.id_matricula, 
	d.cpf_usuario::text as cpf, 
	d.nome_usuario as aluno,
	a.dt_hr_grade_polo as data_encontro, 
	e.is_presente as esta_presenca, 
	'Não definido' as data_registro, 
	f.nome_modulo, 
	case 
		when e.status_grade_aluno is null then 'Pendente'
		when e.status_grade_aluno = 1 then 'Confirmado'
		else 'Não confirmado'
	end as status, 
	(select nome_usuario from app_usuario where id_usuario = a.id_usuario) as professor
from grade_aluno e
inner join grade_polo a on a.id_grade_polo  = e.id_grade_polo 
inner join polo b on b.id_polo  = a.id_polo 
inner join matricula c on c.id_matricula = e.id_matricula
inner join app_usuario d on d.id_usuario = c.id_usuario 
inner join modulo f on f.id_modulo = a.id_modulo
;

-- visão: avaliações de aluno
create or replace view myviews.analytcs_aluno_notas as
select distinct 
	c.cpf_usuario::text, 
	c.nome_usuario, 
	b.id_matricula, 
	nome_modulo,  
	case 
		when a.notas_avaliacao->'avaliacoes'->'ava_1'->>'nota' is not null then coalesce(replace(a.notas_avaliacao->'avaliacoes'->'ava_1'->>'nota', ',', '.'),'0')::decimal 
		else null 
	end as nota1,
	case 
		when a.notas_avaliacao->'avaliacoes'->'ava_2'->>'nota' is not null then coalesce(replace(a.notas_avaliacao->'avaliacoes'->'ava_2'->>'nota', ',', '.'),'0')::decimal 
		else null 
	end as nota2,
	a.notas_avaliacao->'avaliacoes'->'ava_1'->>'date' as data_nota1,
	a.notas_avaliacao->'avaliacoes'->'ava_2'->>'date' as data_nota2,
	case 
		when a.notas_avaliacao->>'ponto_extra' <> '' then coalesce(replace(a.notas_avaliacao->>'ponto_extra', ',', '.'),'0')::decimal
		else null
	end as ponto_extra, 
	case 
		when a.notas_avaliacao->>'recuperacao' <> '' then coalesce(replace(a.notas_avaliacao->>'recuperacao', ',', '.'),'0')::decimal
		else null
	end as recuperacao, 
	faltas_avaliacao as faltas, 
	notafinal_avaliacao as nota_final
from avaliacao a 
inner join matricula b on b.id_matricula = a.id_matricula 
inner join app_usuario c on c.id_usuario = b.id_usuario 
inner join grade_polo d on d.hash_grade_polo = a.cod_turma_avaliacao 
inner join modulo e on e.id_modulo = d.id_modulo









