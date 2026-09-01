-- =====================================================================
-- FIX CRÍTICO: vista faltante vs_vista_recetas
-- =====================================================================
-- Ejecuta esto en phpMyAdmin (pestaña SQL) sobre db_SantaClara.
-- Esta vista no existía, por eso la pantalla de Recetas (c=receta)
-- se caía por completo mostrando un error crudo en vez de la página.
-- =====================================================================

CREATE OR REPLACE VIEW `vs_vista_recetas` AS
SELECT
    t.idTratamiento               AS idTratamiento,
    t.nombre                      AS tratamiento,
    t.descripcion                 AS descripcion,
    c.idConsulta                  AS idConsulta,
    p.ciPaciente                  AS ciPaciente,
    p.nombre                      AS pacienteNombre,
    p.apaterno                    AS pacienteApaterno,
    dr.idMedicamento              AS idMedicamento,
    m.nombre                      AS medicamento,
    dr.dosis                      AS dosis,
    dr.cantidad                   AS cantidad,
    dr.frecuencia                 AS frecuencia,
    dr.viaAdministracion          AS viaAdministracion,
    dr.duracion                   AS duracion,
    t.fechaRegistro                AS fechaRegistro
FROM tratamiento t
JOIN consulta c   ON t.idConsulta = c.idConsulta
JOIN paciente p   ON c.ciPaciente = p.ciPaciente
JOIN detalleReceta dr ON t.idTratamiento = dr.idTratamiento AND dr.estado = 1
JOIN medicamento m ON dr.idMedicamento = m.idMedicamento
WHERE t.estado = 1;
