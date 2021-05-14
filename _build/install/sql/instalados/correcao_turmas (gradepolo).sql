-- Corrigir os hashs
/******************************************- ################# esta deve virar uma funcao de manutencao */
ALTER TABLE public.grade_polo DROP CONSTRAINT grade_polo_un;
execute ('create table manutencao.grade_polo_'||to_char(now(), 'YYMMDD')||'bck select * from grade_polo;');
delete from grade_polo a where exists (
	select 1 from grade_polo b where 
		a.id_polo = b.id_polo 
		and a.id_modulo = b.id_modulo 
		and a.hrainicio_grade_polo = b.hrainicio_grade_polo 
		and to_char(coalesce(a.dt_hr_grade_polo, '2020-01-01'::timestamp), 'YYYY') = to_char(coalesce(b.dt_hr_grade_polo, '2020-01-01'::timestamp), 'YYYY')
		and a.encontro_grade_polo = b.encontro_grade_polo 
		and b.createtime_grade_polo > a.createtime_grade_polo 
);
update grade_polo gp set 
	hash_grade_polo = ns_sha256(id_polo::text || '-' || id_modulo::text  || '-' || hrainicio_grade_polo::text  || '-' || to_char(coalesce(dt_hr_grade_polo, '2020-01-01'::timestamp), 'YYYY'))
	, encontro_grade_polo = (select  (count(*)+1) as encontro_certo from grade_polo where hash_grade_polo= gp.hash_grade_polo and dt_hr_grade_polo < gp.dt_hr_grade_polo)
;
-- recriar o hash unique
ALTER TABLE public.grade_polo ADD CONSTRAINT grade_polo_un UNIQUE (encontro_grade_polo, hash_grade_polo);	   
/******************************************- ################# esta deve virar uma funcao de manutencao */
