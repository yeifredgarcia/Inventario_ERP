-- ============================================================
-- ACTUALIZACIÓN: récipe médico en las órdenes de entrega
-- Úselo SOLO si ya ejecutó antes sql/actualizacion_ordenes.sql (o ya tiene las tablas
-- beneficiarios y ordenes_entrega) y quiere conservar sus datos.
-- Requiere MariaDB (la que trae XAMPP).
-- ============================================================
USE `inventario_medico`;
ALTER TABLE `ordenes_entrega`
  ADD COLUMN IF NOT EXISTS `recipe_archivo` varchar(100) DEFAULT NULL COMMENT 'Archivo del récipe guardado en uploads/recipes',
  ADD COLUMN IF NOT EXISTS `recipe_original` varchar(150) DEFAULT NULL COMMENT 'Nombre original del archivo del récipe';
