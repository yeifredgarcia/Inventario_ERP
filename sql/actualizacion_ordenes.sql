-- ============================================================
-- ACTUALIZACIÓN: beneficiarios y órdenes de entrega
-- Úselo SOLO si ya importó la base anterior y no quiere perder sus datos.
-- Si empieza de cero, importe sql/inventario_medico.sql (ya incluye todo esto).
-- Ejecútelo UNA sola vez (phpMyAdmin > base inventario_medico > pestaña SQL o Importar).
-- Requiere MariaDB (la que trae XAMPP).
-- ============================================================
USE `inventario_medico`;
SET NAMES utf8mb4;

-- Tabla: beneficiarios (registro numerado de las personas a quienes se entrega medicamento)
-- id_beneficiario es el número correlativo del registro (1, 2, 3, ...).
CREATE TABLE IF NOT EXISTS `beneficiarios` (
  `id_beneficiario` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Número correlativo del beneficiario',
  `nombres` varchar(60) NOT NULL,
  `apellidos` varchar(60) NOT NULL,
  `cedula` varchar(10) NOT NULL,
  `tipo_beneficiario` enum('Trabajador','Afiliado') NOT NULL,
  `status` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `usuario_registro` int(10) UNSIGNED DEFAULT NULL,
  `usuario_modificacion` int(10) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id_beneficiario`),
  UNIQUE KEY `uq_beneficiarios_cedula` (`cedula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- Tabla: ordenes_entrega (cada orden agrupa los medicamentos entregados a un beneficiario en una fecha)
-- id_orden es el número correlativo de la orden (se muestra como OE-000001).
CREATE TABLE IF NOT EXISTS `ordenes_entrega` (
  `id_orden` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Número correlativo de la orden',
  `id_beneficiario` int(10) UNSIGNED NOT NULL,
  `fecha_salida` date NOT NULL,
  `observacion` varchar(255) DEFAULT NULL,
  `recipe_archivo` varchar(100) DEFAULT NULL COMMENT 'Archivo del récipe guardado en uploads/recipes',
  `recipe_original` varchar(150) DEFAULT NULL COMMENT 'Nombre original del archivo del récipe',
  `status` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `usuario_registro` int(10) UNSIGNED DEFAULT NULL,
  `usuario_modificacion` int(10) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id_orden`),
  KEY `idx_ord_beneficiario` (`id_beneficiario`),
  KEY `idx_ord_fecha` (`fecha_salida`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;


ALTER TABLE `movimientos_inventario`
  ADD COLUMN IF NOT EXISTS `id_orden` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Orden de entrega a la que pertenece (solo salidas)' AFTER `id_producto`,
  ADD KEY `idx_mov_orden` (`id_orden`);

ALTER TABLE `ordenes_entrega`
  ADD CONSTRAINT `fk_ord_beneficiario` FOREIGN KEY (`id_beneficiario`) REFERENCES `beneficiarios` (`id_beneficiario`) ON UPDATE CASCADE;
ALTER TABLE `movimientos_inventario`
  ADD CONSTRAINT `fk_mov_orden` FOREIGN KEY (`id_orden`) REFERENCES `ordenes_entrega` (`id_orden`) ON UPDATE CASCADE;

ALTER TABLE `beneficiarios`
  ADD CONSTRAINT `fk_ben_reg` FOREIGN KEY (`usuario_registro`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `fk_ben_mod` FOREIGN KEY (`usuario_modificacion`) REFERENCES `usuarios` (`id_usuario`);
ALTER TABLE `ordenes_entrega`
  ADD CONSTRAINT `fk_ord_reg` FOREIGN KEY (`usuario_registro`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `fk_ord_mod` FOREIGN KEY (`usuario_modificacion`) REFERENCES `usuarios` (`id_usuario`);

-- Si la tabla ordenes_entrega ya existía de una actualización anterior, se agregan las columnas del récipe:
ALTER TABLE `ordenes_entrega`
  ADD COLUMN IF NOT EXISTS `recipe_archivo` varchar(100) DEFAULT NULL COMMENT 'Archivo del récipe guardado en uploads/recipes',
  ADD COLUMN IF NOT EXISTS `recipe_original` varchar(150) DEFAULT NULL COMMENT 'Nombre original del archivo del récipe';
