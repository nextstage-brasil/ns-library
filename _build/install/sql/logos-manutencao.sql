rollback;
BEGIN;

SET audit.username TO 'Datacloud/Application';
SET audit.userid TO '-1';

/* 
update matricula a set extras_matricula = x.extras_matricula from manutencao.matricula x where a.id_matricula = x.id_matricula;


create table manutencao.matricula_bkp as select * from matricula;
create table manutencao.financeiro_03032021_1330 as select * from financeiro;


insert into financeiro select * from manutencao.financeiro_03032021_1330
on conflict do nothing;

-- atualização do extras_matriucla, formatação do JSON
create table manutencao.matricula_03032021_1528 as select * from matricula;
update matricula a set extras_matricula = ('{"confirmDate": "'||t.pagamento_financeiro||'", "taxaInscricao": 3000, "confirmIdUsuario": 2}')::jsonb 
from (
	select * from financeiro where id_matricula in (
		select id_matricula  from matricula where not (extras_matricula  ? 'confirmDate') and  id_status_matricula > 2
	) 
	and pagamento_financeiro is not null and id_auxiliar= 2
) t
where a.id_matricula= t.id_matricula


*/

-- Atualização de alunos 2020 por inscrição via sistema
update matricula set extras_matricula = '{"confirmDate": "2020-01-01", "taxaInscricao": 3000, "confirmIdUsuario": 2}'::jsonb
	where obs_matricula ~* '2019|2020'
	and is_inscricao_matricula ='false'
	and not(extras_matricula  @> '{"confirmDate":"2020-01-01"}'::jsonb)
;

update financeiro set valor_financeiro = 0, vencimento_financeiro= '2020-01-31', pagamento_financeiro= '2020-01-31' where id_financeiro in (
	select 
		a.id_financeiro
	from financeiro a
	inner join matricula m ON m.id_matricula = a.id_matricula 
	inner join app_usuario au on au.id_usuario = m.id_usuario 
	where m.extras_matricula  @> '{"confirmDate":"2020-01-01"}'::jsonb
	and a.id_auxiliar = 2
);



commit;