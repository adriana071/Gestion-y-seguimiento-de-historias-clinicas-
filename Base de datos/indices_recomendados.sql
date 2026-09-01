-- =====================================================================
-- ÍNDICES RECOMENDADOS - Hospital Santa Clara
-- =====================================================================
-- Ejecuta este script en phpMyAdmin (pestaña SQL) sobre tu base de
-- datos db_santaclara. Es seguro: solo agrega índices, no borra ni
-- modifica datos existentes.
-- =====================================================================

-- Consulta: se filtra muy seguido por estado y por fecha (dashboard,
-- reportes, listados). Sin índice, cada filtro revisa TODAS las filas.
ALTER TABLE `consulta`
    ADD INDEX `idx_consulta_estadoConsulta` (`estadoConsulta`),
    ADD INDEX `idx_consulta_fechaRegistro` (`fechaRegistro`),
    ADD INDEX `idx_consulta_estado` (`estado`);

-- Examen de laboratorio: se filtra por fecha de solicitud y por estado
-- (activo/inactivo) en los listados y en ContarPendientes().
ALTER TABLE `examenlaboratorio`
    ADD INDEX `idx_examen_fechaSolicitud` (`fechaSolicitud`),
    ADD INDEX `idx_examen_estado` (`estado`);

-- Resultado: se filtra por estadoExamen (Pendiente/Completado) y por
-- estado (activo/inactivo).
ALTER TABLE `resultado`
    ADD INDEX `idx_resultado_estadoExamen` (`estadoExamen`),
    ADD INDEX `idx_resultado_estado` (`estado`);
