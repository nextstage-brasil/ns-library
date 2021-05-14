/*22/02/2021*/
CREATE OR REPLACE FUNCTION public.ns_trg_gradealuno_updatefinanceiro()
RETURNS trigger
LANGUAGE plpgsql
AS $function$
	declare
		financeiro_id int;
		prim_encontro date;
		vencimento date;
		cod_turma text;
		valor decimal;
		res record;
begin

	-- codigo da turma vinculada a esta linha
	select * into res from (
		select 
			min(dt_hr_grade_polo) as prim_encontro,  
			hash_grade_polo as cod_turma, 
			(
				extract(year from (min(dt_hr_grade_polo) + interval '1 month')) 
				|| '-' ||
				extract(month FROM (min(dt_hr_grade_polo) + interval '1 month'))
				|| '-10'
			)::date as vencimento
		from grade_polo a
		where hash_grade_polo = (select hash_grade_polo from grade_polo gp where id_grade_polo = new.id_grade_polo) 
		group by hash_grade_polo 
	) t;

	
	-- Atualizando para não confirmado
	if new.status_grade_aluno is null or new.status_grade_aluno = 2 then
		-- Remover possíveis pendências financeiras vinculadas
		delete from financeiro where id_matricula = new.id_matricula and extras_financeiro @> ('{"cod_turma":"'||res.cod_turma::text||'"}')::jsonb and id_conta is null;
	elseif new.status_grade_aluno = 1 then
		-- Atualizando para confirmado. Inserir pendências financeiras
		select id_financeiro into financeiro_id from financeiro where id_matricula = new.id_matricula and extras_financeiro @> ('{"cod_turma":"'||res.cod_turma::text||'"}')::jsonb;
		raise notice 'id_financeiro is %', financeiro_id;
		if financeiro_id is null then
			-- Obter o valor da mensalidade
			select mens_matricula into valor from matricula where id_matricula = new.id_matricula;
		
			-- Inserir pendencia financeira
			INSERT INTO public.financeiro (id_matricula, valor_financeiro, vencimento_financeiro, descricao_financeiro, extras_financeiro, id_status_financeiro, id_auxiliar) 
				VALUES(new.id_matricula, valor, res.vencimento, '', ('{"cod_turma":"'||res.cod_turma::text||'"}')::jsonb, 1, 1)
				on conflict do nothing;
		end if;
		
	end if;

	RETURN NEW;
END;
$function$
;

-- Criação do trigger
CREATE TRIGGER update_financeiro
    before insert or update
    ON public.grade_aluno
    FOR EACH ROW
    EXECUTE PROCEDURE public.ns_trg_gradealuno_updatefinanceiro();

-- remover todos
update grade_aluno set status_grade_aluno = null;

-- confirmar todos primeiro encontro
update grade_aluno set status_grade_aluno = 1 where id_grade_polo in (
	select id_grade_polo from grade_polo where dt_hr_grade_polo between '2021-02-18' and '2021-03-24'
);

-- remover todos da palhoça
update grade_aluno set status_grade_aluno = null where id_matricula in (
	select id_matricula from matricula where id_polo = 13
) and id_matricula not in (
	select id_matricula from financeiro where id_conta is not null and id_auxiliar = 1
);

   
   
-- em financeiro, anexar o codigo da turma que gerou este lancamento, junto com o codigo do modulo, se houver
update financeiro a set extras_financeiro = extras_financeiro || ('{"cod_turma":"'||t.cod_turma::text||'"}')::jsonb 
from (
	select distinct a.id_financeiro, a.id_matricula, c.hash_grade_polo as cod_turma, a.vencimento_financeiro 
	from financeiro a 
	inner join grade_aluno b using(id_matricula)
	inner join grade_polo c on c.id_grade_polo  = b.id_grade_polo 
	where vencimento_financeiro = '2021-03-10'
	and c.dt_hr_grade_polo= '2021-02-18'
) as t
where a.id_financeiro= t.id_financeiro;



