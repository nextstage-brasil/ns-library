/* 26/04/2021 1857
-- tesoureiro
ALTER TABLE public.polo DROP COLUMN tesoureiro_id;
ALTER TABLE public.polo ADD tesoureiro_id int8 NULL;
COMMENT ON COLUMN public.polo.tesoureiro_id IS 'Tesoureiro do Polo';

-- responsavel
ALTER TABLE public.polo DROP COLUMN responsavel_id;
ALTER TABLE public.polo ADD responsavel_id int8 NULL;
COMMENT ON COLUMN public.polo.responsavel_id IS 'Responsável pelo polo';

update polo set secretaria_id= null;
*/

explain analyze

SELECT financeiro.*, app_auxiliar.*, conta.*, app_forma_pgto.*, matricula.*, app_status.* FROM financeiro left JOIN app_auxiliar ON app_auxiliar.id_auxiliar = financeiro.id_auxiliar 
left JOIN conta ON conta.id_conta = financeiro.id_conta 
left JOIN app_forma_pgto ON app_forma_pgto.id_forma_pgto = financeiro.id_forma_pgto 
left JOIN matricula ON matricula.id_matricula = financeiro.id_matricula 
left JOIN app_status ON app_status.id_status = financeiro.id_status_financeiro 
ORDER BY financeiro.id_financeiro ASC LIMIT 50 OFFSET 0;-- {ORIGEM: ConnectionPDO::executeQuery:432}
