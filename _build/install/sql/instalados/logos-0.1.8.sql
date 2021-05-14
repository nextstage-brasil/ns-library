/* OK em 05/01/2021
begin;

ALTER TABLE public.matricula ADD CONSTRAINT matricula_un UNIQUE (id_curso,id_usuario);
ALTER TABLE public.grade_aluno ADD CONSTRAINT grade_aluno_un UNIQUE (id_grade_polo,id_matricula);
ALTER TABLE public.app_auxiliar ALTER COLUMN extras_auxiliar DROP NOT NULL;
INSERT INTO public.app_auxiliar (id_auxiliar,id_empresa,nome_auxiliar) VALUES (1,1,'Mensalidade');
INSERT INTO public.app_auxiliar (id_auxiliar,id_empresa,nome_auxiliar) VALUES (2,1,'Inscrição');
ALTER TABLE public.financeiro ADD id_auxiliar int2 NOT NULL DEFAULT 1;
COMMENT ON COLUMN public.financeiro.id_auxiliar IS 'Tipo da origem do lançamento';
INSERT INTO public.app_status (nome_status,entidade_status,color_status) VALUES ('Aguardando pagamento','Financeiro','red');
ALTER TABLE public.app_status ADD order_status int2 NULL;
COMMENT ON COLUMN public.app_status.order_status IS 'Ordem do status';
UPDATE public.app_status SET order_status=1 WHERE id_status=1;
UPDATE public.app_status	SET entidade_status='FINANCEIRO',order_status=1 WHERE id_status=1;
ALTER TABLE public.financeiro ADD CONSTRAINT financeiro_auxiliar_fk FOREIGN KEY (id_auxiliar) REFERENCES public.app_auxiliar(id_auxiliar) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE public.matricula ADD id_status int2 NULL;
COMMENT ON COLUMN public.matricula.id_status IS 'Status';
ALTER TABLE public.matricula ADD CONSTRAINT matricula_status_fk FOREIGN KEY (id_status) REFERENCES public.app_status(id_status) ON DELETE RESTRICT ON UPDATE CASCADE;
INSERT INTO public.app_status (nome_status,entidade_status,color_status,order_status) VALUES ('Inscrição aguardando pagamento','MATRICULA','red',1);
INSERT INTO public.app_status (nome_status,entidade_status,color_status,order_status) VALUES ('Inscrição aguardando pagamento','MATRICULA','red',1);
UPDATE public.app_status SET color_status='danger'WHERE id_status=1;
UPDATE public.app_status SET color_status='danger'WHERE id_status=2;
UPDATE public.app_status SET color_status='success',nome_status='Inscrição confirmada',order_status=2 WHERE id_status=3;
UPDATE public.app_status SET nome_status='Inscrição confirmada, pagamento recebido' WHERE id_status=3;
ALTER TABLE public.matricula RENAME COLUMN id_status TO id_status_matricula;
ALTER TABLE public.financeiro RENAME COLUMN id_status TO id_status_financeiro;
ALTER TABLE public.matricula ALTER COLUMN id_status_matricula SET NOT NULL;
ALTER TABLE public.financeiro ALTER COLUMN id_status_financeiro SET NOT NULL;
commit;
*/

/* 06/01/2021
rollback;
begin;
INSERT INTO public.app_status (nome_status,entidade_status,color_status,order_status) VALUES ('Pagamento registrado','FINANCEIRO','success',100);
-- public.forma_pgto definition
CREATE TABLE public.forma_pgto (
	id_forma_pgto int2 NOT NULL, 
	nome_forma_pgto varchar(100) NOT NULL, -- Nome|Nome da forma de pagamento
	tp_forma_pgto int2 NOT NULL, -- Tipo do pagamento
	CONSTRAINT forma_pgto_pk PRIMARY KEY (id_forma_pgto)
);
COMMENT ON TABLE public.forma_pgto IS 'Cadastro de formas de pagamento';
COMMENT ON COLUMN public.forma_pgto.id_forma_pgto IS 'Forma de pagamento';
COMMENT ON COLUMN public.forma_pgto.nome_forma_pgto IS 'Nome|Nome da forma de pagamento';
COMMENT ON COLUMN public.forma_pgto.tp_forma_pgto IS 'Tipo do pagamento|Tipo 1: A receber, 2: a pagar';
ALTER TABLE public.forma_pgto RENAME TO app_forma_pgto;
-- Auto-generated SQL script #202101061537
INSERT INTO public.app_forma_pgto (id_forma_pgto,nome_forma_pgto,tp_forma_pgto) VALUES 
(1,'NÃO DEFINIDO',3),
(2,'Em espécie',3), 
(3,'Boleto bancário',3), 
(4,'Cartão de crédito',1), 
(5,'Cartão de débito',3), 
(6,'PIX',3),
(7,'Cheque',3),
(8,'TED/Transferência bancária',3)
;
ALTER TABLE public.financeiro ADD id_forma_pgto int2 NOT NULL DEFAULT 1;
COMMENT ON COLUMN public.financeiro.id_forma_pgto IS 'Forma de pagamento escolhida';
UPDATE public.app_forma_pgto
	SET id_forma_pgto=108
	WHERE id_forma_pgto=8;
UPDATE public.app_forma_pgto
	SET id_forma_pgto=107
	WHERE id_forma_pgto=7;
UPDATE public.app_forma_pgto
	SET id_forma_pgto=206
	WHERE id_forma_pgto=6;
UPDATE public.app_forma_pgto
	SET id_forma_pgto=205
	WHERE id_forma_pgto=5;
UPDATE public.app_forma_pgto
	SET id_forma_pgto=204
	WHERE id_forma_pgto=4;
UPDATE public.app_forma_pgto
	SET id_forma_pgto=203
	WHERE id_forma_pgto=3;
UPDATE public.app_forma_pgto
	SET id_forma_pgto=102
	WHERE id_forma_pgto=2;
INSERT INTO public.app_status (nome_status,entidade_status,color_status,order_status)
	VALUES ('Pagamento finalizado','FINANCEIRO','success',100);
UPDATE public.app_status
	SET order_status=90,nome_status='Pagamento registrado - aguardando confirmação'
	WHERE id_status=4;
ALTER TABLE public.financeiro ADD pagamento_financeiro date NULL;
COMMENT ON COLUMN public.financeiro.pagamento_financeiro IS 'Data que foi confirmado o pagamento deste lançamento';

-- public.fin_conta definition

CREATE TABLE public.conta (
	id_conta serial NOT NULL,
	createtime_conta timestamp NULL DEFAULT now(),
	id_empresa int4 NOT NULL,
	tipo_conta varchar(1) NOT NULL DEFAULT 'C'::character varying, -- Tipo de conta|Conta corrente ou Conta investimento
	nome_conta varchar(250) NULL, -- Nome da conta
	banco_conta varchar(50) NULL, -- Nome do banco
	agencia_conta varchar(6) NULL, -- Número da agência
	numero_conta varchar(15) NULL, -- Número da conta
	saldoatual_conta numeric(12,2) NULL,
	datasaldo_conta timestamp NULL,
	status_conta int2 NOT NULL DEFAULT 1,
	extras_conta jsonb NULL, -- Saldo OFX|Ultimo saldo lido por OFX
	is_contacaixa_conta bool NOT NULL DEFAULT true, -- É conta caixa?|Define se a conta é conta caixa, com valores em espécie.
	id_banco varchar(3) NOT NULL DEFAULT '000'::character varying, -- Banco relacionado
	CONSTRAINT conta_pkey PRIMARY KEY (id_conta)
);
CREATE INDEX fin_conta_tipoconta_idx ON conta USING btree (tipo_conta);

-- Column comments

COMMENT ON COLUMN public.conta.tipo_conta IS 'Tipo de conta|Conta corrente ou Conta investimento';
COMMENT ON COLUMN public.conta.nome_conta IS 'Nome da conta';
COMMENT ON COLUMN public.conta.banco_conta IS 'Nome do banco';
COMMENT ON COLUMN public.conta.agencia_conta IS 'Número da agência';
COMMENT ON COLUMN public.conta.numero_conta IS 'Número da conta';
COMMENT ON COLUMN public.conta.is_contacaixa_conta IS 'É conta caixa?|Define se a conta é conta caixa, com valores em espécie.';
COMMENT ON COLUMN public.conta.id_banco IS 'Banco relacionado';

ALTER TABLE public.financeiro ADD id_conta int4 NULL;
COMMENT ON COLUMN public.financeiro.id_conta IS 'Conta relacionada com o lancamento';
ALTER TABLE public.financeiro ADD CONSTRAINT financeiro_conta_fk FOREIGN KEY (id_conta) REFERENCES public.conta(id_conta) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE public.conta ADD id_polo int4 NOT NULL;
COMMENT ON COLUMN public.conta.id_polo IS 'Polo relacionado a esta conta';
ALTER TABLE public.conta ADD CONSTRAINT conta_empresa_fk FOREIGN KEY (id_empresa) REFERENCES public.app_empresa(id_empresa) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE public.conta ADD CONSTRAINT conta_polo_fk FOREIGN KEY (id_polo) REFERENCES public.polo(id_polo) ON DELETE RESTRICT ON UPDATE CASCADE;
UPDATE public.app_status
	SET order_status=100
	WHERE id_status=3;
INSERT INTO public.conta (createtime_conta, id_empresa, tipo_conta, nome_conta, banco_conta, agencia_conta, numero_conta, saldoatual_conta, datasaldo_conta, status_conta, extras_conta, is_contacaixa_conta, id_banco, id_polo) VALUES('2021-01-06 18:57:51.807', 1, 'C', 'Conta caixa', '', '', '', NULL, NULL, 1, '{"a_definir": "Modelo não definido: extrasConta"}', true, '0', 10);
INSERT INTO public.conta (createtime_conta, id_empresa, tipo_conta, nome_conta, banco_conta, agencia_conta, numero_conta, saldoatual_conta, datasaldo_conta, status_conta, extras_conta, is_contacaixa_conta, id_banco, id_polo) VALUES('2021-01-06 19:21:08.881', 1, 'C', 'Conta caixa', '', '', '', NULL, NULL, 1, '{"a_definir": "Modelo não definido: extrasConta"}', true, '0', 11);
ALTER TABLE public.financeiro ADD CONSTRAINT financeiro_forma_pgto_fk FOREIGN KEY (id_forma_pgto) REFERENCES public.app_forma_pgto(id_forma_pgto) ON DELETE RESTRICT ON UPDATE CASCADE;

INSERT INTO public.app_status (id_status, nome_status, entidade_status, descricao_status, color_status, order_status) VALUES(2, 'Inscrição aguardando pagamento', 'MATRICULA', NULL, 'danger', 1);
INSERT INTO public.app_status (id_status, nome_status, entidade_status, descricao_status, color_status, order_status) VALUES(1, 'Aguardando pagamento', 'FINANCEIRO', NULL, 'danger', 1);
INSERT INTO public.app_status (id_status, nome_status, entidade_status, descricao_status, color_status, order_status) VALUES(5, 'Pagamento finalizado', 'FINANCEIRO', NULL, 'success', 100);
INSERT INTO public.app_status (id_status, nome_status, entidade_status, descricao_status, color_status, order_status) VALUES(4, 'Pagamento registrado - aguardando confirmação', 'FINANCEIRO', NULL, 'success', 90);
INSERT INTO public.app_status (id_status, nome_status, entidade_status, descricao_status, color_status, order_status) VALUES(3, 'Inscrição confirmada, pagamento recebido', 'MATRICULA', NULL, 'success', 100);
alter sequence app_status_id_status_seq restart with 6;



commit;

*/

/* 12/01/2021
begin;
update app_usuario set cpf_usuario = '99999999999';
COMMENT ON COLUMN public.app_usuario.cpf_usuario IS 'CPF| Será o login do usuário';
COMMENT ON COLUMN public.app_usuario.email_usuario IS 'E-mail';
commit;
*/

/* 13/01/2021
begin;
COMMENT ON COLUMN public.avaliacao.nota_avaliacao IS 'Nota final da avaliação';
COMMENT ON COLUMN public.avaliacao.data_avaliacao IS 'Data de lançamento';
ALTER TABLE public.avaliacao RENAME COLUMN nota_avaliacao TO notafinal_avaliacao;
COMMENT ON COLUMN public.avaliacao.notafinal_avaliacao IS 'Nota final da avaliação';
COMMENT ON COLUMN public.avaliacao.data_avaliacao IS 'Data de lançamento';
ALTER TABLE public.avaliacao ADD cod_turma_avaliacao varchar NULL;
COMMENT ON COLUMN public.avaliacao.cod_turma_avaliacao IS 'Código da turma vinculada com esta avaliacao';
ALTER TABLE public.avaliacao ADD faltas_avaliacao int2 NOT NULL DEFAULT 0;
COMMENT ON COLUMN public.avaliacao.faltas_avaliacao IS 'Quantidade de faltas registradas';
ALTER TABLE public.avaliacao ADD notas_avaliacao jsonb NULL;
COMMENT ON COLUMN public.avaliacao.notas_avaliacao IS 'Conjuntos de notas conforme configuração do módulo';
ALTER TABLE public.avaliacao ALTER COLUMN cod_turma_avaliacao SET NOT NULL;
ALTER TABLE public.avaliacao DROP COLUMN notafinal_avaliacao;
ALTER TABLE public.avaliacao ADD notafinal_avaliacao decimal(12,2) NOT null; -- Nota final da avaliação;
COMMENT ON COLUMN public.avaliacao.notafinal_avaliacao IS 'Nota final da avaliação';
ALTER TABLE public.avaliacao DROP COLUMN data_avaliacao;
commit;
*/

/* 14/01/2021
rollback;
begin;

TRUNCATE TABLE public.app_sistema_funcao RESTART IDENTITY CASCADE;
INSERT INTO public.app_usuario (id_usuario, createtime_usuario, id_empresa, nome_usuario, tipo_usuario, perfil_usuario, email_usuario, senha_usuario, data_nascimento_usuario, sexo_usuario, rg_usuario, cpf_usuario, token_altera_senha_usuario, cep_usuario, complemento_cep_usuario, token_validade_usuario, ult_acesso_usuario, data_senha_usuario, session_time_usuario, avatar_usuario, is_alive_usuario, extras_usuario, link_public_usuario, status_usuario) VALUES(10, '2021-01-14 19:14:52.123', 1, 'Usuário Básico', 1, 1, 'Usuario_Basico@perfil', 'PENDENTE', NULL, 'M', '', '', '', '', '', NULL, NULL, NULL, 60, NULL, true, '{"Descrição": "Perfil principal de qualquer usuário do sistema, sem permissão de acesso a nada, exceto o que seu papel permite.", "isProfessor": "Não"}', '', 1) on conflict do nothing;
INSERT INTO public.app_usuario (id_usuario, createtime_usuario, id_empresa, nome_usuario, tipo_usuario, perfil_usuario, email_usuario, senha_usuario, data_nascimento_usuario, sexo_usuario, rg_usuario, cpf_usuario, token_altera_senha_usuario, cep_usuario, complemento_cep_usuario, token_validade_usuario, ult_acesso_usuario, data_senha_usuario, session_time_usuario, avatar_usuario, is_alive_usuario, extras_usuario, link_public_usuario, status_usuario) VALUES(11, '2021-01-14 18:54:44.602', 1, 'Administrador', 1, 1, 'Administrador@perfil', 'PENDENTE', NULL, 'M', '', '', '', '', '', NULL, NULL, NULL, 60, NULL, true, '{"Descrição": null, "isProfessor": "Não"}', '', 1) on conflict do nothing;


INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(197, 'XGRADE', 'XGRADE', 'LER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(196, 'XGRADE', 'XGRADE', 'REMOVER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(195, 'XGRADE', 'XGRADE', 'EDITAR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(194, 'XGRADE', 'XGRADE', 'INSERIR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(193, 'SISTEMA', 'USUARIO', 'INSERIR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(187, 'SISTEMA', 'INTEGRACAO', 'LER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(184, 'SISTEMA', 'INTEGRACAO', 'INSERIR/EDITAR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(183, 'USUARIO', 'PODERES', 'EDITAR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(179, 'SISTEMA', 'USUARIO', 'EDITAR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(177, 'SISTEMA', 'USUARIO', 'GERENCIAR PERFIS');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(171, 'SISTEMA', 'USUARIO', 'GERENCIAR PERMISSÕES');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(170, 'USUARIO', 'USUARIO', 'LER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(169, 'USUARIO', 'USUARIO', 'REMOVER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(168, 'USUARIO', 'USUARIO', 'EDITAR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(167, 'USUARIO', 'USUARIO', 'INSERIR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(166, 'UF', 'UF', 'LER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(165, 'UF', 'UF', 'REMOVER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(164, 'UF', 'UF', 'EDITAR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(163, 'UF', 'UF', 'INSERIR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(162, 'SOLICITACAOCOMPRA', 'SOLICITACAOCOMPRA', 'LER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(161, 'SOLICITACAOCOMPRA', 'SOLICITACAOCOMPRA', 'REMOVER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(160, 'SOLICITACAOCOMPRA', 'SOLICITACAOCOMPRA', 'EDITAR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(159, 'SOLICITACAOCOMPRA', 'SOLICITACAOCOMPRA', 'INSERIR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(158, 'SISTEMA', 'USUARIO', 'LER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(157, 'SITE', 'SITE', 'LER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(156, 'SITE', 'SITE', 'REMOVER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(155, 'SITE', 'SITE', 'EDITAR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(154, 'SITE', 'SITE', 'INSERIR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(153, 'SISTEMALOG', 'SISTEMALOG', 'LER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(152, 'SISTEMALOG', 'SISTEMALOG', 'REMOVER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(151, 'SISTEMALOG', 'SISTEMALOG', 'EDITAR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(150, 'SISTEMALOG', 'SISTEMALOG', 'INSERIR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(149, 'SISTEMAFUNCAO', 'SISTEMAFUNCAO', 'LER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(148, 'SISTEMAFUNCAO', 'SISTEMAFUNCAO', 'REMOVER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(147, 'SISTEMAFUNCAO', 'SISTEMAFUNCAO', 'EDITAR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(146, 'SISTEMAFUNCAO', 'SISTEMAFUNCAO', 'INSERIR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(145, 'SHAREDUSER', 'SHAREDUSER', 'LER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(144, 'SHAREDUSER', 'SHAREDUSER', 'REMOVER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(143, 'SHAREDUSER', 'SHAREDUSER', 'EDITAR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(142, 'SHAREDUSER', 'SHAREDUSER', 'INSERIR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(141, 'SHARED', 'SHARED', 'LER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(140, 'SHARED', 'SHARED', 'REMOVER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(139, 'SHARED', 'SHARED', 'EDITAR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(138, 'SHARED', 'SHARED', 'INSERIR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(137, 'REEMBOLSOMOTIVO', 'REEMBOLSOMOTIVO', 'LER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(136, 'REEMBOLSOMOTIVO', 'REEMBOLSOMOTIVO', 'REMOVER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(135, 'REEMBOLSOMOTIVO', 'REEMBOLSOMOTIVO', 'EDITAR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(134, 'REEMBOLSOMOTIVO', 'REEMBOLSOMOTIVO', 'INSERIR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(133, 'REEMBOLSO', 'REEMBOLSO', 'LER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(132, 'REEMBOLSO', 'REEMBOLSO', 'REMOVER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(131, 'REEMBOLSO', 'REEMBOLSO', 'EDITAR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(130, 'REEMBOLSO', 'REEMBOLSO', 'INSERIR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(129, 'POSTREAD', 'POSTREAD', 'LER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(128, 'POSTREAD', 'POSTREAD', 'REMOVER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(127, 'POSTREAD', 'POSTREAD', 'EDITAR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(126, 'POSTREAD', 'POSTREAD', 'INSERIR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(125, 'POST', 'POST', 'LER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(124, 'POST', 'POST', 'REMOVER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(123, 'POST', 'POST', 'EDITAR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(122, 'POST', 'POST', 'INSERIR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(121, 'POLO', 'POLO', 'LER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(120, 'POLO', 'POLO', 'REMOVER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(119, 'POLO', 'POLO', 'EDITAR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(118, 'POLO', 'POLO', 'INSERIR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(117, 'PAIS', 'PAIS', 'LER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(116, 'PAIS', 'PAIS', 'REMOVER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(115, 'PAIS', 'PAIS', 'EDITAR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(114, 'PAIS', 'PAIS', 'INSERIR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(113, 'MUNICIPIO', 'MUNICIPIO', 'LER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(112, 'MUNICIPIO', 'MUNICIPIO', 'REMOVER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(111, 'MUNICIPIO', 'MUNICIPIO', 'EDITAR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(110, 'MUNICIPIO', 'MUNICIPIO', 'INSERIR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(109, 'MODULO', 'MODULO', 'LER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(108, 'MODULO', 'MODULO', 'REMOVER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(107, 'MODULO', 'MODULO', 'EDITAR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(106, 'MODULO', 'MODULO', 'INSERIR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(105, 'MENSAGEMGRUPOUSERS', 'MENSAGEMGRUPOUSERS', 'LER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(104, 'MENSAGEMGRUPOUSERS', 'MENSAGEMGRUPOUSERS', 'REMOVER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(103, 'MENSAGEMGRUPOUSERS', 'MENSAGEMGRUPOUSERS', 'EDITAR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(102, 'MENSAGEMGRUPOUSERS', 'MENSAGEMGRUPOUSERS', 'INSERIR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(101, 'MENSAGEMGRUPO', 'MENSAGEMGRUPO', 'LER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(100, 'MENSAGEMGRUPO', 'MENSAGEMGRUPO', 'REMOVER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(99, 'MENSAGEMGRUPO', 'MENSAGEMGRUPO', 'EDITAR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(98, 'MENSAGEMGRUPO', 'MENSAGEMGRUPO', 'INSERIR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(97, 'MATRICULA', 'MATRICULA', 'LER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(96, 'MATRICULA', 'MATRICULA', 'REMOVER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(95, 'MATRICULA', 'MATRICULA', 'EDITAR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(94, 'MATRICULA', 'MATRICULA', 'INSERIR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(93, 'LTREL', 'LTREL', 'LER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(92, 'LTREL', 'LTREL', 'REMOVER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(91, 'LTREL', 'LTREL', 'EDITAR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(90, 'LTREL', 'LTREL', 'INSERIR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(89, 'LOGINATTEMPTS', 'LOGINATTEMPTS', 'LER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(88, 'LOGINATTEMPTS', 'LOGINATTEMPTS', 'REMOVER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(87, 'LOGINATTEMPTS', 'LOGINATTEMPTS', 'EDITAR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(86, 'LOGINATTEMPTS', 'LOGINATTEMPTS', 'INSERIR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(83, 'LOGOS', 'LINKTABLE', 'LISTAR');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(82, 'LINKTABLE', 'LINKTABLE', 'LER');
INSERT INTO public.app_sistema_funcao (id_sistema_funcao, grupo_funcao, subgrupo_funcao, acao_funcao) VALUES(81, 'LINKTABLE', 'LINKTABLE', 'REMOVER');



-- Todos os perfis para o usuário administrador
insert into app_usuario_permissao (id_sistema_funcao, id_usuario)
	select id_sistema_funcao, 11 from app_sistema_funcao
	on conflict do nothing;

alter sequence app_sistema_funcao_id_sistema_funcao_seq restart with 1000;

ALTER TABLE public.app_usuario ADD numero_cep_usuario varchar(50) NULL;
COMMENT ON COLUMN public.app_usuario.numero_cep_usuario IS 'Número|Número da residência, prédio, localização';


commit;



/* 
-- Reset
truncate table matricula cascade;
delete from app_usuario where id_usuario  > 49;
truncate table grade_aluno;

*/
/*
-- Auto-generated SQL script #202101141750
DELETE FROM public.financeiro WHERE id_matricula=(select id_matricula from matricula where id_usuario=3);
DELETE FROM public.matricula  WHERE id_matricula=(select id_matricula from matricula where id_usuario=3);
*/

/* 15/01/2021
begin;
COMMENT ON COLUMN public.app_usuario.complemento_cep_usuario IS 'Complemento|Apartamento, Bloco, Entrada...';
CREATE TABLE public.escolaridade (
	id_escolaridade serial NOT null, 
	nome_escolaridade varchar(100) NOT NULL,
	order_escolaridade int2 NULL, -- Ordem de exibição dos campos
	CONSTRAINT escolaridade_pkey PRIMARY KEY (id_escolaridade)
);
COMMENT ON TABLE public.escolaridade IS 'Cadastro de escolaridade';
INSERT INTO public.escolaridade (id_escolaridade, nome_escolaridade, order_escolaridade) VALUES(1, 'Fundamental - Incompleto', 3);
INSERT INTO public.escolaridade (id_escolaridade, nome_escolaridade, order_escolaridade) VALUES(2, 'Fundamental - Completo', 4);
INSERT INTO public.escolaridade (id_escolaridade, nome_escolaridade, order_escolaridade) VALUES(3, 'Médio - Incompleto', 5);
INSERT INTO public.escolaridade (id_escolaridade, nome_escolaridade, order_escolaridade) VALUES(4, 'Médio  - Completo', 6);
INSERT INTO public.escolaridade (id_escolaridade, nome_escolaridade, order_escolaridade) VALUES(5, 'Superior - Incompleto', 7);
INSERT INTO public.escolaridade (id_escolaridade, nome_escolaridade, order_escolaridade) VALUES(6, 'Superior - Completo', 8);
INSERT INTO public.escolaridade (id_escolaridade, nome_escolaridade, order_escolaridade) VALUES(7, 'Pós-graduação (Lato senso) - Incompleto', 9);
INSERT INTO public.escolaridade (id_escolaridade, nome_escolaridade, order_escolaridade) VALUES(8, 'Pós-graduação (Lato senso) - Completo', 10);
INSERT INTO public.escolaridade (id_escolaridade, nome_escolaridade, order_escolaridade) VALUES(9, 'Pós-graduação (Stricto sensu, nível mestrado) - Incompleto', 11);
INSERT INTO public.escolaridade (id_escolaridade, nome_escolaridade, order_escolaridade) VALUES(10, 'Pós-graduação (Stricto sensu, nível mestrado) - Completo', 12);
INSERT INTO public.escolaridade (id_escolaridade, nome_escolaridade, order_escolaridade) VALUES(11, 'Pós-graduação (Stricto sensu, nível doutor) - Incompleto', 13);
INSERT INTO public.escolaridade (id_escolaridade, nome_escolaridade, order_escolaridade) VALUES(12, 'Pós-graduação (Stricto sensu, nível doutor) - Completo', 14);
INSERT INTO public.escolaridade (id_escolaridade, nome_escolaridade, order_escolaridade) VALUES(33, 'Analfabeto', 1);
INSERT INTO public.escolaridade (id_escolaridade, nome_escolaridade, order_escolaridade) VALUES(34, 'Alfabetizado', 2);
alter sequence escolaridade_id_escolaridade_seq restart with 35;

commit;

-- CREATE EXTENSION pgcrypto;

CREATE OR REPLACE FUNCTION ns_sha256(text)
 	RETURNS text
 	LANGUAGE sql
 	IMMUTABLE STRICT
	AS $function$
	    SELECT encode(digest($1, 'sha256'), 'hex')
	$function$
;

select ns_sha256('cristofer');
*/

/* 19/01/2021
begin;
ALTER TABLE public.matricula ADD id_polo int8 NOT NULL DEFAULT 10;
COMMENT ON COLUMN public.matricula.id_polo IS 'Polo';
ALTER TABLE public.matricula ALTER COLUMN id_polo DROP DEFAULT;
ALTER TABLE public.matricula ADD CONSTRAINT matricula_polo_fk FOREIGN KEY (id_polo) REFERENCES public.polo(id_polo) ON DELETE RESTRICT ON UPDATE CASCADE;
commit;
*/

/* 25/01/2021
rollback;
begin;
INSERT INTO public.app_usuario (id_usuario, createtime_usuario, id_empresa, nome_usuario, tipo_usuario, perfil_usuario, email_usuario, senha_usuario, data_nascimento_usuario, sexo_usuario, rg_usuario, cpf_usuario, token_altera_senha_usuario, cep_usuario, complemento_cep_usuario, token_validade_usuario, ult_acesso_usuario, data_senha_usuario, session_time_usuario, avatar_usuario, is_alive_usuario, extras_usuario, link_public_usuario, status_usuario, numero_cep_usuario) VALUES(12, '2021-01-26 13:50:06.341', 1, 'Secretaria', 1, 1, 'secretaria@perfil', 'PENDENTE', NULL, 'M', '', '', '', '', '', NULL, NULL, NULL, 60, NULL, true, '{"Descrição": "Funções de secretaria", "isProfessor": "Não"}', '', 1, '');
INSERT INTO public.app_usuario (id_usuario, createtime_usuario, id_empresa, nome_usuario, tipo_usuario, perfil_usuario, email_usuario, senha_usuario, data_nascimento_usuario, sexo_usuario, rg_usuario, cpf_usuario, token_altera_senha_usuario, cep_usuario, complemento_cep_usuario, token_validade_usuario, ult_acesso_usuario, data_senha_usuario, session_time_usuario, avatar_usuario, is_alive_usuario, extras_usuario, link_public_usuario, status_usuario, numero_cep_usuario) VALUES(13, '2021-01-26 13:50:21.152', 1, 'Professor', 1, 1, 'professor@perfil', 'PENDENTE', NULL, 'M', '', '', '', '', '', NULL, NULL, NULL, 60, NULL, true, '{"Descrição": "Funções do professor além do papel conforme a turma", "isProfessor": "Não"}', '', 1, '');
update app_usuario set perfil_usuario= 13 where extras_usuario @> '{"isProfessor":"Sim"}'::jsonb;

ALTER TABLE public.grade_polo ALTER COLUMN hash_grade_polo TYPE varchar(64) USING hash_grade_polo::varchar;
CREATE INDEX grade_polo_id_polo_idx ON public.grade_polo (id_polo,dt_hr_grade_polo);





delete from app_sistema_funcao a where exists (
	select 1 from app_sistema_funcao b where a.grupo_funcao = b.grupo_funcao and a.subgrupo_funcao =b.subgrupo_funcao and a.acao_funcao = b.acao_funcao 
	and b.id_sistema_funcao  < a.id_sistema_funcao 
);
ALTER TABLE public.app_sistema_funcao ADD CONSTRAINT app_sistema_funcao_un UNIQUE (acao_funcao,grupo_funcao,subgrupo_funcao);
update app_usuario set email_usuario = lower(email_usuario);

delete from app_usuario where id_usuario= 54;
-- Auto-generated SQL script #202101261335
UPDATE public.app_usuario
	SET email_usuario='10'
	WHERE id_usuario=44;
UPDATE public.app_usuario
	SET email_usuario='20'
	WHERE id_usuario=42;
UPDATE public.app_usuario
	SET email_usuario='30'
	WHERE id_usuario=2;
UPDATE public.app_usuario
	SET email_usuario='40'
	WHERE id_usuario=36;
UPDATE public.app_usuario
	SET email_usuario='50'
	WHERE id_usuario=29;
ALTER TABLE public.app_usuario ADD CONSTRAINT app_usuario_un UNIQUE (email_usuario);

INSERT INTO public.app_sistema_funcao (grupo_funcao, subgrupo_funcao, acao_funcao) VALUES('CONTA', 'CONTA', 'REMOVER');
delete from app_sistema_funcao	WHERE id_sistema_funcao=183;
ALTER TABLE public.app_sistema_funcao ADD extras_funcao json NULL;
ALTER TABLE public.avaliacao ALTER COLUMN faltas_avaliacao DROP NOT NULL;
ALTER TABLE public.avaliacao ALTER COLUMN faltas_avaliacao DROP DEFAULT;
ALTER TABLE public.avaliacao ALTER COLUMN notafinal_avaliacao DROP NOT NULL;



INSERT INTO public.app_sistema_funcao (grupo_funcao, subgrupo_funcao, acao_funcao, extras_funcao) VALUES('CONTA', 'CONTA', 'EDITAR', NULL);
INSERT INTO public.app_sistema_funcao (grupo_funcao, subgrupo_funcao, acao_funcao, extras_funcao) VALUES('GRADEALUNO', 'GRADEALUNO', 'REGISTRAR PRESENÇA', NULL);






commit;
 /* */


/* 26/01/2021 */
-- Aqui


/* 08/02/2021
BEGIN;
ALTER TABLE public.grade_aluno ADD status_grade_aluno int NULL;
COMMENT ON COLUMN public.grade_aluno.status_grade_aluno IS '1: Aceito, 2: Recusado. null: Não tratado.';
ALTER TABLE public.grade_aluno ADD obs_grade_aluno text NULL;
COMMENT ON COLUMN public.grade_aluno.obs_grade_aluno IS 'Observações';
ALTER TABLE public.grade_aluno ADD datastatus_grade_aluno date NULL;
COMMENT ON COLUMN public.grade_aluno.datastatus_grade_aluno IS 'Data do último status';
COMMIT;
*/

CREATE INDEX grade_polo_hash_grade_polo_idx ON public.grade_polo USING btree (hash_grade_polo);

/*22/02/2021*/
 * 
 * 
 */
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
		delete from financeiro where id_matricula = new.id_matricula and extras_financeiro @> ('{"cod_turma":"'||res.cod_turma::text||'"}')::jsonb;
	elseif new.status_grade_aluno = 1 then
		-- Atualizando para confirmado. Inserir pendências financeiras
		select id_financeiro into financeiro_id from financeiro where id_matricula = new.id_matricula and extras_financeiro @> ('{"cod_turma":"'||res.cod_turma::text||'"}')::jsonb;
		raise notice 'id_financeiro is %', financeiro_id;
		if financeiro_id is null then
			-- Obter o valor da mensalidade
			select mens_matricula into valor from matricula where id_matricula = new.id_matricula;
		
			-- Inserir pendencia financeira
			INSERT INTO public.financeiro (id_matricula, valor_financeiro, vencimento_financeiro, descricao_financeiro, extras_financeiro, id_status_financeiro, id_auxiliar) 
				VALUES(new.id_matricula, valor, res.vencimento, '', ('{"cod_turma":"'||res.cod_turma::text||'"}')::jsonb, 1, 1);
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



