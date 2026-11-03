-- ============================================================
-- ACTUALIZACIÓN: documentos (V, E, RIF, pasaporte), teléfono, número de récipe y limpieza de insumos
-- Úselo SOLO si ya tiene la base con las tablas beneficiarios y ordenes_entrega y quiere conservar sus datos.
-- Si empieza de cero, importe sql/inventario_medico.sql (ya incluye todo).
-- Ejecútelo UNA sola vez. Requiere MariaDB (la que trae XAMPP).
-- ============================================================
USE `inventario_medico`;
SET NAMES utf8mb4;

-- 1) Beneficiarios: tipo de documento, número más largo y teléfono
ALTER TABLE `beneficiarios`
  ADD COLUMN IF NOT EXISTS `tipo_documento` enum('V','E','RIF','P') NOT NULL DEFAULT 'V' COMMENT 'V cédula venezolana, E cédula de extranjero, RIF o P pasaporte' AFTER `apellidos`,
  MODIFY COLUMN `cedula` varchar(20) NOT NULL COMMENT 'Número del documento, sin guiones (en RIF incluye la letra)',
  ADD COLUMN IF NOT EXISTS `telefono` varchar(20) DEFAULT NULL AFTER `cedula`;
-- Los beneficiarios que ya existían quedan como cédula venezolana (V); corrija a mano los que no lo sean.
ALTER TABLE `beneficiarios` DROP INDEX IF EXISTS `uq_beneficiarios_cedula`;
ALTER TABLE `beneficiarios` ADD UNIQUE KEY `uq_beneficiarios_documento` (`tipo_documento`,`cedula`);

-- 2) Movimientos: el documento de la persona ahora puede ser más largo (V-12345678, RIF J-12345678-9...)
ALTER TABLE `movimientos_inventario` MODIFY COLUMN `cedula_persona` varchar(20) NOT NULL;

-- 3) Órdenes: número del récipe de la persona
ALTER TABLE `ordenes_entrega`
  ADD COLUMN IF NOT EXISTS `recipe_numero` varchar(30) DEFAULT NULL COMMENT 'Número del récipe de la persona' AFTER `recipe_original`;

-- 4) Quitar de Medicamentos los insumos 453 a 458 (SOLUCION 0,9, GLUCOSA, RINGER, DEXTROSA, AGUA OXIGENADA y ALCOHOL):
--    se borran si nunca tuvieron movimientos; si ya tienen historial, quedan Inactivos para conservarlo.
DELETE FROM `inventario_general`
 WHERE `id_producto` BETWEEN 453 AND 458
   AND `id_producto` NOT IN (SELECT `id_producto` FROM `movimientos_inventario`);
UPDATE `inventario_general` SET `status` = 'Inactivo' WHERE `id_producto` BETWEEN 453 AND 458;
