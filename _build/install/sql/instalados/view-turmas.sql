-- myviews.turmas source

CREATE OR REPLACE VIEW myviews.turmas
AS SELECT DISTINCT ON (c.hash_grade_polo) c.hash_grade_polo AS id,
    ( SELECT min(gp.dt_hr_grade_polo) AS min
           FROM grade_polo gp
          WHERE gp.hash_grade_polo::text = c.hash_grade_polo::text) AS prim_encontro,
    ( SELECT max(gp.dt_hr_grade_polo) AS max
           FROM grade_polo gp
          WHERE gp.hash_grade_polo::text = c.hash_grade_polo::text) AS ult_encontro,
    e.id_polo,
    e.nome_polo,
    m.id_modulo,
    m.nome_modulo,
    c.id_usuario,
    n.nome_usuario,
    ( SELECT count(DISTINCT grade_aluno.id_matricula) AS count
           FROM grade_aluno
          WHERE (grade_aluno.id_grade_polo IN ( SELECT grade_polo.id_grade_polo
                   FROM grade_polo
                  WHERE grade_polo.hash_grade_polo::text = c.hash_grade_polo::text))) AS qtde_alunos
   FROM grade_polo c
     JOIN polo e ON e.id_polo = c.id_polo
     JOIN modulo m ON m.id_modulo = c.id_modulo
     JOIN app_usuario n ON n.id_usuario = c.id_usuario;