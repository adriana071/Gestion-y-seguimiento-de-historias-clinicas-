-- =====================================================================
-- BLOQUEO DE CUENTA POR INTENTOS FALLIDOS - Hospital Santa Clara
-- =====================================================================
-- Ejecuta esto en phpMyAdmin (pestaña SQL) sobre db_santaclara.
-- Agrega 2 columnas nuevas a `usuario`, no borra ni modifica datos.
-- =====================================================================

ALTER TABLE `usuario`
    ADD COLUMN `intentosFallidos` INT NOT NULL DEFAULT 0 AFTER `password`,
    ADD COLUMN `bloqueadoHasta` DATETIME NULL DEFAULT NULL AFTER `intentosFallidos`;
