rollback;
BEGIN;

set audit.username = 'Cristofer';
set audit.userid = '1';


select audit.audit_table('app_status');
select audit.audit_table('app_linktable');
select audit.audit_table('app_usuario');
select audit.audit_table('avaliacao');
select audit.audit_table('bolsa');
select audit.audit_table('conta');
select audit.audit_table('curso');
select audit.audit_table('escolaridade');
select audit.audit_table('financeiro');
select audit.audit_table('forum');
select audit.audit_table('grade_aluno');
select audit.audit_table('grade_polo');
select audit.audit_table('indisp');
select audit.audit_table('leciona');
select audit.audit_table('matricula');
select audit.audit_table('modulo');
select audit.audit_table('polo');
select audit.audit_table('reembolso');
select audit.audit_table('reembolso_motivo');
select audit.audit_table('solicitacao_compra');

       
          
commit;