-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 18-09-2026 a las 03:08:50
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `inventario_medico`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL,
  `nombre_categoria` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nombre_categoria`) VALUES
(5, 'ayudas_tecnicas'),
(3, 'blister'),
(1, 'medicamentos'),
(2, 'medico_quirurquico'),
(4, 'oftalmologia');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario_general`
--

CREATE TABLE `inventario_general` (
  `id_producto` bigint(20) UNSIGNED NOT NULL,
  `nombre_producto` varchar(150) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `id_presentacion` bigint(20) UNSIGNED NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 0 CHECK (`cantidad` >= 0),
  `fecha_vencimiento` date DEFAULT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'Disponible' CHECK (`estado` in ('Disponible','Vencido','Por Vencer'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `inventario_general`
--

INSERT INTO `inventario_general` (`id_producto`, `nombre_producto`, `id_categoria`, `id_presentacion`, `cantidad`, `fecha_vencimiento`, `estado`) VALUES
(1, 'ACIDO TRANEXANICO', 1, 1, 250, '2028-02-01', 'Disponible'),
(2, 'ADRENALINA', 1, 1, 168, '2027-06-01', 'Disponible'),
(3, 'AMIKACINA', 1, 1, 435, '2028-08-01', 'Disponible'),
(4, 'AMIODARONA', 1, 1, 54, '2027-05-01', 'Disponible'),
(5, 'AMOXICILIN A+ ACIDO CLAVULANICO', 1, 1, 14, '2026-11-01', 'Por Vencer'),
(6, 'AMOXICILINA 250 MG', 1, 9, 2, '2027-07-01', 'Disponible'),
(7, 'AMPICILINA SULBATAN', 1, 1, 35, '2028-01-01', 'Disponible'),
(8, 'ATROPINA 0,5', 1, 1, 304, '2027-07-01', 'Disponible'),
(9, 'ATROPINA 1 MG', 1, 1, 221, '2026-09-01', 'Vencido'),
(10, 'BETAMETASONA 4 MG', 1, 1, 13, '2026-08-01', 'Vencido'),
(11, 'BROMURO DE IPATROPIO', 1, 2, 0, NULL, 'Disponible'),
(12, 'BUDESONIDA', 1, 10, 1, '2028-01-01', 'Disponible'),
(13, 'BUDESONIDA', 1, 2, 3, '2027-02-01', 'Disponible'),
(14, 'BUPIVACAINA 0,5', 1, 1, 180, '2026-09-01', 'Vencido'),
(15, 'BUPIVACAINA 50 MG', 1, 1, 14, '2027-11-01', 'Disponible'),
(16, 'CEFAZOLINA', 1, 1, 339, '2027-03-01', 'Disponible'),
(17, 'CIPROFLOXACINA 200 MG', 1, 1, 200, '2027-10-01', 'Disponible'),
(18, 'CLINDAMICINA 600 MG', 1, 1, 15, '2027-08-01', 'Disponible'),
(19, 'CLOBETASOL', 1, 3, 7, '2027-12-01', 'Disponible'),
(20, 'CLONIDINA 0,150 MG', 1, 1, 38, '2027-06-01', 'Disponible'),
(21, 'CLORURO DE POTASIO 2 MG', 1, 1, 3, '2027-07-01', 'Disponible'),
(22, 'CLORURO DE SODIO 20%', 1, 1, 134, '2027-07-01', 'Disponible'),
(23, 'COMPLEJO B', 1, 1, 266, '2028-01-01', 'Disponible'),
(24, 'DEXAMETASONA 4 MG', 1, 1, 113, '2028-09-01', 'Disponible'),
(25, 'DEXAMETASONA 8 MG', 1, 1, 534, '2027-04-01', 'Disponible'),
(26, 'DEXMEDETOMIDINA 200 MCG', 1, 1, 12, '2027-02-01', 'Disponible'),
(27, 'DICLOFENAC SODICO 75 MG', 1, 1, 141, '2027-01-01', 'Disponible'),
(28, 'DIPIRONA 1 GR', 1, 1, 274, '2027-07-01', 'Disponible'),
(29, 'DIPIRONA 1.5 GR', 1, 1, 450, '2027-03-01', 'Disponible'),
(30, 'EFEDRINA 10 MG', 1, 1, 86, '2028-08-01', 'Disponible'),
(31, 'ENALAPRILAT 1,25 MG', 1, 1, 80, '2027-12-01', 'Disponible'),
(32, 'ENOXAPARINA SODICA 40 MG', 1, 1, 11, '2027-11-01', 'Disponible'),
(33, 'ENOXAPARINA SODICA 60 MG', 1, 1, 12, '2028-02-01', 'Disponible'),
(34, 'EFEDRINA 10 MG', 1, 1, 11, '2028-05-01', 'Disponible'),
(35, 'EPHEDRINA SULFAT O,05 G', 1, 1, 9, '2027-07-01', 'Disponible'),
(36, 'Esmolol HCl 100 mg', 1, 1, 10, '2026-10-01', 'Por Vencer'),
(37, 'ETAMSIALTO 250 MG', 1, 1, 74, '2027-06-01', 'Disponible'),
(38, 'FENOBARBITAL SODICO 200 MG', 1, 1, 10, '2027-04-01', 'Disponible'),
(39, 'FENOTEC 5 MG', 1, 2, 10, '2026-11-01', 'Por Vencer'),
(40, 'FITOMENADIONA 10 MG', 1, 1, 8, '2027-07-01', 'Disponible'),
(41, 'FLUCONAZOL 200 MG', 1, 5, 3, '2027-04-01', 'Disponible'),
(42, 'FUROSEMIDA 20 MG', 1, 1, 1600, '2028-09-01', 'Disponible'),
(43, 'METILPREDNISOLONA 500 MG', 1, 1, 34, '2026-07-01', 'Vencido'),
(44, 'GENTAMICINA 80 MG', 1, 1, 60, '2027-11-01', 'Disponible'),
(45, 'GLUCONATO DE CALCIO 10%', 1, 1, 101, '2028-08-01', 'Disponible'),
(46, 'HALOPERIDOL 5 MG', 1, 1, 10, '2028-06-01', 'Disponible'),
(47, 'HEPARINA SODICA 5000 UI', 1, 1, 14, '2027-11-01', 'Disponible'),
(48, 'HIDROCORTISONA 100 MG', 1, 1, 43, '2027-07-01', 'Disponible'),
(49, 'HIDROCORTISONA 500 MG', 1, 1, 175, '2027-08-01', 'Disponible'),
(50, 'HIOSCINA N BUTIL BROMURO 20 MG', 1, 1, 259, '2028-01-01', 'Disponible'),
(51, 'HIOSCINA COMPUESTA 20 MG', 1, 1, 180, '2027-11-01', 'Disponible'),
(52, 'IBUPROFENO 100 MG', 1, 9, 2, '2027-03-01', 'Disponible'),
(53, 'KETOPROFENO 100 MG', 1, 1, 431, '2028-08-01', 'Disponible'),
(54, 'KETOROLAC TROMETAMINA 30 MG', 1, 1, 450, '2028-07-01', 'Disponible'),
(55, 'LABETALOL 100 MG', 1, 1, 10, '2027-03-01', 'Disponible'),
(56, 'SALBUTAMOL GOTAS 5 MG', 1, 2, 250, '2026-09-01', 'Vencido'),
(57, 'LIDOCAINA 2% S/E 20 ML', 1, 1, 55, '2027-08-01', 'Disponible'),
(58, 'LIDOCAINA 2% 10 ML', 1, 1, 312, '2028-02-01', 'Disponible'),
(59, 'LIDOCAINA HEAVY 5%', 1, 1, 18, '2027-11-01', 'Disponible'),
(60, 'LOPERAMIDA 2 MG', 1, 6, 1, '2027-06-01', 'Disponible'),
(61, 'MANITOL 20%', 1, 5, 3, '2027-08-01', 'Disponible'),
(62, 'METOCLOPRAMIDA 10 MG', 1, 1, 860, '2028-08-01', 'Disponible'),
(63, 'MIDAZOLAM 15 MG', 1, 1, 2, '2028-04-01', 'Disponible'),
(64, 'ACIDOCLOVIR 15 G', 1, 3, 52, '2025-10-01', 'Vencido'),
(65, 'ATROPINA 1 MG', 1, 8, 1523, '2025-10-01', 'Vencido'),
(66, 'BETAMTSONA 4 MG', 1, 8, 30, '2026-04-01', 'Vencido'),
(67, 'BUPIVACAINA', 1, 8, 65, '2025-03-01', 'Vencido'),
(68, 'CIFARCAINA 1% 100 ML', 1, 4, 5, '2025-11-01', 'Vencido'),
(69, 'CIFARCAINA HIPERBARICA', 1, 8, 29, '2026-07-01', 'Vencido'),
(70, 'CITICOLINA SODICA 500 ML', 1, 8, 124, '2024-05-01', 'Vencido'),
(71, 'CLORFENIRAMINA 10 MG', 1, 8, 16, '2025-12-01', 'Vencido'),
(72, 'CLOXACILIN 240 MG', 1, 8, 20, '2025-08-01', 'Vencido'),
(73, 'DIAZEPAM 5 MG', 1, 8, 10, '2026-01-01', 'Vencido'),
(74, 'DOBOTAMINE 250 MG', 1, 8, 80, '2026-01-01', 'Vencido'),
(75, 'DURACAINA HIPERBARA 5 MG', 1, 8, 113, '2025-04-01', 'Vencido'),
(76, 'EFEDRINA AL 6% 1 ML', 1, 8, 559, '2025-03-01', 'Vencido'),
(77, 'FENITOINA 250 MG', 1, 8, 4, '2026-05-01', 'Vencido'),
(78, 'FENTANILO O.5 MG', 1, 8, 10, '2025-09-01', 'Vencido'),
(79, 'FITOMENADIONA 10 MG', 1, 8, 1465, '2025-12-01', 'Vencido'),
(80, 'FLUCONAZOLE 100 MG', 1, 5, 41, '2025-03-01', 'Vencido'),
(81, 'HALOPERIDOL 5 MG', 1, 8, 50, '2025-12-01', 'Vencido'),
(82, 'HIDROCORTISONA 500 MG', 1, 8, 408, '2026-02-01', 'Vencido'),
(83, 'ISOFLURANE USP 100 ML', 1, 4, 7, '2026-01-01', 'Vencido'),
(84, 'ISOFLURANO USP 30 ML', 1, 4, 1, '2024-07-01', 'Vencido'),
(85, 'ISOFLURANO 100 ML', 1, 4, 30, '2019-06-01', 'Vencido'),
(86, 'LIDOCAINA 1% 50 ML', 1, 4, 5, '2026-04-01', 'Vencido'),
(87, 'LIDOCAINA 2% 50 ML', 1, 8, 6, '2026-05-01', 'Vencido'),
(88, 'MEROPENEM 1 G', 1, 8, 40, '2026-06-01', 'Vencido'),
(89, 'MEROPENEM 1 G', 1, 8, 168, '2026-04-01', 'Vencido'),
(90, 'MIDAZOLAM 10 MG', 1, 8, 50, '2025-10-01', 'Vencido'),
(91, 'MORFINA 10 MG', 1, 8, 53, '2024-11-01', 'Vencido'),
(92, 'NEITROGLICERINA 50 MG', 1, 8, 14, '2025-06-01', 'Vencido'),
(93, 'NEOSTIGMINA 0.5 MG', 1, 8, 1930, '2026-01-01', 'Vencido'),
(94, 'RINOMAX', 1, 2, 36, '2025-06-01', 'Vencido'),
(95, 'RINOSAL S', 1, 2, 32, '2025-06-01', 'Vencido'),
(96, 'NALOXONA 0.4 MG', 1, 1, 18, '2027-02-01', 'Disponible'),
(97, 'NEOSTIGMINA 0,5 MG', 1, 1, 355, '2028-02-01', 'Disponible'),
(98, 'NIFEDIPINA 10 MG', 1, 2, 10, '2027-06-01', 'Disponible'),
(99, 'NITROPRUSIATO DE SODIO 50 MG', 1, 1, 10, '2028-01-01', 'Disponible'),
(100, 'NOREPINEFRINA 4 MG', 1, 1, 50, '2028-06-01', 'Disponible'),
(101, 'OMEPRAZOL 40 MG', 1, 1, 80, '2027-11-01', 'Disponible'),
(102, 'ONDANSETRON 8 MG', 1, 1, 259, '2027-12-01', 'Disponible'),
(103, 'OXITOCINA 10 UI', 1, 1, 490, '2028-08-01', 'Disponible'),
(104, 'PARACETAMOL 10 MG', 1, 5, 35, '2027-08-01', 'Disponible'),
(105, 'PENICILINA BENZATINICA 1.200 UI', 1, 1, 1, '2026-11-01', 'Por Vencer'),
(106, 'PENTOXIFILINA 100 MG', 1, 1, 110, '2027-06-01', 'Disponible'),
(107, 'PIPIRACILINA TAZOBACTAN 4.5 G', 1, 1, 40, '2027-05-01', 'Disponible'),
(108, 'POLIVITAMINAS', 1, 1, 50, '2028-08-01', 'Disponible'),
(109, 'PROPANOLOL 1 MG', 1, 1, 10, '2027-03-01', 'Disponible'),
(110, 'PROPOFOL 1% 20 ML', 1, 1, 20, '2027-11-01', 'Disponible'),
(111, 'PROSTAGLANDINA 500 MCG', 1, 1, 2, '2027-01-01', 'Disponible'),
(112, 'PROTAMINA SULFATO 50 MG', 1, 1, 10, '2027-01-01', 'Disponible'),
(113, 'RANITIDINA 50 MG', 1, 1, 1500, '2027-11-01', 'Disponible'),
(114, 'REMOFENTANILO 2 MG', 1, 1, 4, '2027-06-01', 'Disponible'),
(115, 'ROCURONIO BROMURO 50 MG', 1, 1, 20, '2027-06-01', 'Disponible'),
(116, 'SALBUTAMOL 0,5%', 1, 2, 1, '2027-11-01', 'Disponible'),
(117, 'SALBUTAMOL SPRAY 100 MCG', 1, 10, 1, '2027-05-01', 'Disponible'),
(118, 'SOLUCION FISIOLOGICA 0.9%', 1, 5, 0, NULL, 'Disponible'),
(119, 'SULFATO DE MAGNESIO 1 G', 1, 1, 24, '2028-05-01', 'Disponible'),
(120, 'SUXAMETONIO BROMURO 100 MG', 1, 1, 180, '2027-06-01', 'Disponible'),
(121, 'TRAMADOL 100 MG', 1, 1, 300, '2027-05-01', 'Disponible'),
(122, 'TRAMADOL 50 MG', 1, 1, 383, '2028-07-01', 'Disponible'),
(123, 'VANCOMICINA 500 MG', 1, 1, 21, '2027-10-01', 'Disponible'),
(124, 'VECURONIO BROMURO 4 MG', 1, 1, 150, '2028-08-01', 'Disponible'),
(125, 'VITAMINA C 1 G', 1, 1, 184, '2027-03-01', 'Disponible'),
(126, 'VITAMINA K 10 MG', 1, 1, 220, '2027-11-01', 'Disponible'),
(127, 'ADHESIVO MICROPORE N 1', 2, 12, 265, NULL, 'Disponible'),
(128, 'ADHESIVO MICROPORE N 3', 2, 8, 50, NULL, 'Disponible'),
(129, 'AGUJA HIPODERMICA 21 G', 2, 13, 200, NULL, 'Disponible'),
(130, 'AGUJA HIPODERMICAS 23 G', 2, 13, 1132, NULL, 'Disponible'),
(131, 'AGUJA HIPODERMICAS 27 G', 2, 13, 4300, NULL, 'Disponible'),
(132, 'AGUJA HIPODERMICAS 30 G', 2, 13, 4600, NULL, 'Disponible'),
(133, 'AGUJAS HIPODERMICA N 25 G', 2, 13, 200, NULL, 'Disponible'),
(134, 'AGUJAS HIPODERMICAS N 18 G', 2, 13, 33, NULL, 'Disponible'),
(135, 'AGUJAS HIPODERMICAS N 20 G', 2, 13, 180, NULL, 'Disponible'),
(136, 'AGUJAS HIPODERMICAS N 22 G', 2, 13, 532, NULL, 'Disponible'),
(137, 'AGUJAS PARA LAPICERO DE INSULINA 31 G', 2, 13, 180, NULL, 'Disponible'),
(138, 'AGUJAS RAQUIDEA 22 G', 2, 13, 20, NULL, 'Disponible'),
(139, 'AGUJAS RAQUIDEA 25 G', 2, 13, 3, NULL, 'Disponible'),
(140, 'AGUJAS RAQUIDEA N 26 G', 2, 13, 1, NULL, 'Disponible'),
(141, 'AGUJAS RAQUIDEAS 27 G', 2, 13, 300, NULL, 'Disponible'),
(142, 'AGUJAS RAQUIDEAS N 23 G', 2, 13, 2, NULL, 'Disponible'),
(143, 'ALGODÓN', 2, 12, 1, NULL, 'Disponible'),
(144, 'ALMOHADILLAS CON ALCOHOL', 2, 13, 35, NULL, 'Disponible'),
(145, 'APÓSITO', 2, 13, 2, NULL, 'Disponible'),
(146, 'APÓSITO TRANSPARENTE CON ALMOHADILLA', 2, 13, 4, NULL, 'Disponible'),
(147, 'BANDA O MANGUERA DE ESFICNOMANOMETRO', 2, 13, 1, NULL, 'Disponible'),
(148, 'BISTURI N 11', 2, 13, 97, NULL, 'Disponible'),
(149, 'BISTURI N 15', 2, 13, 89, NULL, 'Disponible'),
(150, 'BISTURÍ Nº 20', 2, 13, 3, NULL, 'Disponible'),
(151, 'BISTURÍ Nº 22', 2, 13, 25, NULL, 'Disponible'),
(152, 'BISTURÍ Nº 23', 2, 13, 23, NULL, 'Disponible'),
(153, 'BOLSA ADULTOS DE RESUCITADOR REUTILIZABLE', 2, 13, 2, NULL, 'Disponible'),
(154, 'BOLSA DE COLOSTOMÍA', 2, 13, 2, NULL, 'Disponible'),
(155, 'BOLSAS COLECTORAS DE ORINA', 2, 13, 29, NULL, 'Disponible'),
(156, 'BARRERAS PROTECTORAS', 2, 13, 1, NULL, 'Disponible'),
(157, 'BRAZALETE DE IDENTIFICACIÓN NIÑO', 2, 13, 4, NULL, 'Disponible'),
(158, 'BOQUILLAS DE ESPIRÓMETRO', 2, 13, 4, NULL, 'Disponible'),
(159, 'BOMBILLA REUTILIZABLE CON MANGUERA', 2, 13, 1, NULL, 'Disponible'),
(160, 'BOMBILLA TIPO PERA MANUAL DE ESFIGNOMANOMETRO', 2, 13, 1, NULL, 'Disponible'),
(161, 'CEPILLO QUIRÚRGICO', 2, 13, 11, NULL, 'Disponible'),
(162, 'CÁNULA NASAL ADULTO', 2, 13, 13, NULL, 'Disponible'),
(163, 'CÁNULA NASAL PEDIÁTRICO', 2, 13, 1, NULL, 'Disponible'),
(164, 'CÁNULA NASAL NEONATAL', 2, 13, 2, NULL, 'Disponible'),
(165, 'CÁNULA DE TRAQUEOSTOMÍA PEDIÁTRICA N 3,5', 2, 13, 2, NULL, 'Disponible'),
(166, 'CÁNULA DE TRAQUEOSTOMÍA N 6,0', 2, 13, 1, NULL, 'Disponible'),
(167, 'CÁNULA DE TRAQUEOSTOMÍA N 6,5', 2, 13, 1, NULL, 'Disponible'),
(168, 'CÁNULA DE TRAQUEOSTOMÍA N 7,5', 2, 13, 1, NULL, 'Disponible'),
(169, 'CÁNULA DE GUEDEL N 0', 2, 13, 10, NULL, 'Disponible'),
(170, 'CÁNULA DE GUEDEL N 1', 2, 13, 1, NULL, 'Disponible'),
(171, 'CÁNULA DE GUEDEL N 2', 2, 13, 1, NULL, 'Disponible'),
(172, 'CÁNULA DE GUEDEL N 3', 2, 13, 1, NULL, 'Disponible'),
(173, 'CÁNULA DE GUEDEL N 4', 2, 13, 1, NULL, 'Disponible'),
(174, 'CATÉTER PERIFÉRICO N 16 G', 2, 13, 1, NULL, 'Disponible'),
(175, 'CATÉTER PERIFÉRICO N 18 G', 2, 13, 80, NULL, 'Disponible'),
(176, 'CATÉTER PERIFÉRICO N 20 G', 2, 13, 40, NULL, 'Disponible'),
(177, 'CATÉTER PERIFÉRICO N 22 G', 2, 13, 1, NULL, 'Disponible'),
(178, 'CATÉTER PERIFÉRICO N 24 G', 2, 13, 20, NULL, 'Disponible'),
(179, 'CATÉTER PERIFÉRICO EPICUTÁNEO CAVAL 24 G', 2, 13, 1, NULL, 'Disponible'),
(180, 'CATÉTER TENCKHOFF', 2, 13, 1, NULL, 'Disponible'),
(181, 'CATÉTER VENOSO CENTRAL ADULTO 7 FR 20 CM', 2, 13, 1, NULL, 'Disponible'),
(182, 'CATÉTER VENOSO CENTRAL NEONATAL 3 FR', 2, 13, 1, NULL, 'Disponible'),
(183, 'CINTA METRICA', 2, 13, 1, NULL, 'Disponible'),
(184, 'CINTA METRICA ADHESIVA DESECHABLE', 2, 13, 30, NULL, 'Disponible'),
(185, 'CINTA ADHESIVA PARA TENSION', 2, 13, 1, NULL, 'Disponible'),
(186, 'CINTAS DE GLICEMIA CAPILAR', 2, 13, 50, NULL, 'Disponible'),
(187, 'CIRCUITO DE ANESTESIA ADULTO DESECHABLE', 2, 13, 4, NULL, 'Disponible'),
(188, 'CIRCUITO DE VENTILACIÓN PEDIÁTRICO', 2, 13, 1, NULL, 'Disponible'),
(189, 'COMPRESA NO ESTERIL', 2, 13, 10, NULL, 'Disponible'),
(190, 'CONECTOR TIPO Y PARA SUCCIÓN DE VIDRIO', 2, 13, 2, NULL, 'Disponible'),
(191, 'CONECTOR TIPO Y PARA VENTILADOR PEDIÁTRICO', 2, 13, 2, NULL, 'Disponible'),
(192, 'CEPILLO LIMPIADOR DE TRAQUEOSTOMÍA', 2, 13, 1, NULL, 'Disponible'),
(193, 'CUPULA DE MARCA PASO', 2, 13, 1, NULL, 'Disponible'),
(194, 'CEPILLO CITOBRUSH', 2, 13, 150, NULL, 'Disponible'),
(195, 'CUPULA TRANSPARENTE DE MASCARILLA DE RESUCITADOR', 2, 13, 1, NULL, 'Disponible'),
(196, 'ELECTRODOS PARA ADULTOS', 2, 13, 80, NULL, 'Disponible'),
(197, 'ELECTRODOS PEDIÁTRICO', 2, 13, 14, NULL, 'Disponible'),
(198, 'EQUIPO DESECHABLE DE BOMBA DE INFUSIÓN ALARIS', 2, 13, 2, NULL, 'Disponible'),
(199, 'EQUIPO DESECHABLE DE INFUSIÓN PARENTERAL FOTOSENSIBLE', 2, 13, 1, NULL, 'Disponible'),
(200, 'EQUIPO DE PERFUSIÓN MACROGOTERO', 2, 13, 155, NULL, 'Disponible'),
(201, 'EQUIPO DE PERFUSIÓN MICROGOTERO CON BURETA', 2, 13, 10, NULL, 'Disponible'),
(202, 'EQUIPO DE PERFUSIÓN MACROGOTERO CON BURETA', 2, 13, 1, NULL, 'Disponible'),
(203, 'ESPATULAS DE AYRE DE MADERA', 2, 13, 2, NULL, 'Disponible'),
(204, 'ESPECULO VAGINAL DESECHABLE MEDIANO', 2, 13, 200, NULL, 'Disponible'),
(205, 'FILTRO DE BACTERIA', 2, 13, 1, NULL, 'Disponible'),
(206, 'FIXOMULL TRANSPARENTE 10 CM X 10 M', 2, 12, 1, NULL, 'Disponible'),
(207, 'GASAS NO ESTÉRILES', 2, 13, 1, NULL, 'Disponible'),
(208, 'GEL DE ECOGRAFÍA', 2, 14, 1, NULL, 'Disponible'),
(209, 'GEL DE LUBRICANTE LUBRIFLEX', 2, 4, 1, NULL, 'Disponible'),
(210, 'GORROS DESECHABLES', 2, 13, 100, NULL, 'Disponible'),
(211, 'GUANTES EXAMINACIÓN TALLA S', 2, 13, 100, NULL, 'Disponible'),
(212, 'GUANTES EXAMINACIÓN TALLA M', 2, 13, 100, NULL, 'Disponible'),
(213, 'GUANTES ESTÉRILES DE CIRUGÍA TALLA 6,5', 2, 13, 50, NULL, 'Disponible'),
(214, 'GUANTES ESTÉRILES DE CIRUGÍA TALLA 7,0', 2, 13, 50, NULL, 'Disponible'),
(215, 'GUANTES ESTÉRILES DE CIRUGÍA TALLA 7,5', 2, 13, 50, NULL, 'Disponible'),
(216, 'GUANTES ESTÉRILES DE CIRUGÍA TALLA 8,0', 2, 13, 50, NULL, 'Disponible'),
(217, 'GUÍA INTRODUCTORA DE TUBO ENDOTRAQUEAL ADULTO', 2, 13, 1, NULL, 'Disponible'),
(218, 'GUÍA INTRODUCTORA DE TUBO ENDOTRAQUEAL PEDIÁTRICO', 2, 13, 1, NULL, 'Disponible'),
(219, 'HOJA DE BISTURI N 11', 2, 13, 100, NULL, 'Disponible'),
(220, 'HOJA DE BISTURI N 15', 2, 13, 100, NULL, 'Disponible'),
(221, 'INCENTIVO RESPIRATORIO VOLUMÉTRICO', 2, 13, 1, NULL, 'Disponible'),
(222, 'Incentivo respiratorio volumétrico pediátrico', 2, 13, 1, NULL, 'Disponible'),
(223, 'MASCARILLA DE ANESTESIA N 0', 2, 13, 1, NULL, 'Disponible'),
(224, 'MASCARILLA DE ANESTESIA N 1', 2, 13, 1, NULL, 'Disponible'),
(225, 'MASCARILLA DE ANESTESIA N 2', 2, 13, 1, NULL, 'Disponible'),
(226, 'MASCARILLA DE ANESTESIA N 3', 2, 13, 1, NULL, 'Disponible'),
(227, 'MASCARILLA DE ANESTESIA N 4', 2, 13, 1, NULL, 'Disponible'),
(228, 'MASCARILLA DE ANESTESIA N 5', 2, 13, 1, NULL, 'Disponible'),
(229, 'MASCARILLA LARÍNGEA N 1', 2, 13, 1, NULL, 'Disponible'),
(230, 'MASCARILLA LARÍNGEA N 1,5', 2, 13, 1, NULL, 'Disponible'),
(231, 'MASCARILLA LARÍNGEA N 2', 2, 13, 1, NULL, 'Disponible'),
(232, 'MASCARILLA LARÍNGEA N 2,5', 2, 13, 1, NULL, 'Disponible'),
(233, 'MASCARILLA LARÍNGEA N 3', 2, 13, 1, NULL, 'Disponible'),
(234, 'MASCARILLA LARÍNGEA N 4', 2, 13, 1, NULL, 'Disponible'),
(235, 'MASCARILLA LARÍNGEA N 5', 2, 13, 1, NULL, 'Disponible'),
(236, 'MASCARILLA PARA NEBULIZAR ADULTO', 2, 13, 2, NULL, 'Disponible'),
(237, 'MASCARILLA PARA NEBULIZAR PEDIÁTRICA', 2, 13, 1, NULL, 'Disponible'),
(238, 'MASCARILLA PARA OXÍGENO SIMPLE ADULTO', 2, 13, 1, NULL, 'Disponible'),
(239, 'MASCARILLA PARA OXÍGENO CON RESERVORIO ADULTO', 2, 13, 1, NULL, 'Disponible'),
(240, 'MASCARILLA TAPABOCA QUIRÚRGICO DESECHABLE', 2, 15, 1, NULL, 'Disponible'),
(241, 'NIPLE CONECTOR O ADAPTADOR PARA OXÍGENO', 2, 13, 1, NULL, 'Disponible'),
(242, 'OBTURADOR DE SEGURIDAD TAPÓN LUER LOCK', 2, 13, 1, NULL, 'Disponible'),
(243, 'PAÑAL ADULTO TALLA M', 2, 13, 10, NULL, 'Disponible'),
(244, 'PAÑAL ADULTO TALLA L', 2, 13, 10, NULL, 'Disponible'),
(245, 'PAPEL PARA ELECTROCARDIOGRAMA', 2, 12, 1, NULL, 'Disponible'),
(246, 'PROTETOR BUCAL PARA ENDOSCOPIA', 2, 13, 1, NULL, 'Disponible'),
(247, 'RECIPIENTE O HUMIFICADOR PARA OXÍGENO', 2, 13, 1, NULL, 'Disponible'),
(248, 'RELECTOR O ESPÉCULO NASAL PEDIÁTRICO', 2, 13, 1, NULL, 'Disponible'),
(249, 'SÁBANA DESECHABLE PARA CAMILLA', 2, 13, 1, NULL, 'Disponible'),
(250, 'SENSOR DE OXIMETRÍA DE PULSO ADULTO REUTILIZABLE', 2, 13, 1, NULL, 'Disponible'),
(251, 'SENSORES DE TEMPERATURA REUTILIZABLE', 2, 13, 1, NULL, 'Disponible'),
(252, 'SONDA ASPIRACIÓN DE FLEMA N 6', 2, 13, 10, NULL, 'Disponible'),
(253, 'SONDA ASPIRACIÓN DE FLEMA N 8', 2, 13, 10, NULL, 'Disponible'),
(254, 'SONDA ASPIRACIÓN DE FLEMA N 10', 2, 13, 10, NULL, 'Disponible'),
(255, 'SONDA ASPIRACIÓN DE FLEMA N 12', 2, 13, 10, NULL, 'Disponible'),
(256, 'SONDA ASPIRACIÓN DE FLEMA N 14', 2, 13, 10, NULL, 'Disponible'),
(257, 'SONDA ASPIRACIÓN DE FLEMA N 16', 2, 13, 10, NULL, 'Disponible'),
(258, 'SONDA ASPIRACIÓN DE FLEMA N 18', 2, 13, 10, NULL, 'Disponible'),
(259, 'SONDA FOLEY 2 VÍAS N 12', 2, 13, 5, NULL, 'Disponible'),
(260, 'SONDA FOLEY 2 VÍAS N 14', 2, 13, 5, NULL, 'Disponible'),
(261, 'SONDA FOLEY 2 VÍAS N 16', 2, 13, 10, NULL, 'Disponible'),
(262, 'SONDA FOLEY 2 VÍAS N 18', 2, 13, 10, NULL, 'Disponible'),
(263, 'SONDA FOLEY 2 VÍAS N 20', 2, 13, 5, NULL, 'Disponible'),
(264, 'SONDA FOLEY 2 VÍAS N 22', 2, 13, 5, NULL, 'Disponible'),
(265, 'SONDA FOLEY 3 VÍAS N 18', 2, 13, 2, NULL, 'Disponible'),
(266, 'SONDA FOLEY 3 VÍAS N 20', 2, 13, 2, NULL, 'Disponible'),
(267, 'SONDA FOLEY 3 VÍAS N 22', 2, 13, 2, NULL, 'Disponible'),
(268, 'SONDA FOLEY 3 VÍAS N 24', 2, 13, 2, NULL, 'Disponible'),
(269, 'SONDA NASOGÁSTRICA N 8', 2, 13, 5, NULL, 'Disponible'),
(270, 'SONDA NASOGÁSTRICA N 10', 2, 13, 5, NULL, 'Disponible'),
(271, 'SONDA NASOGÁSTRICA N 12', 2, 13, 5, NULL, 'Disponible'),
(272, 'SONDA NASOGÁSTRICA N 14', 2, 13, 5, NULL, 'Disponible'),
(273, 'SONDA NASOGÁSTRICA N 16', 2, 13, 5, NULL, 'Disponible'),
(274, 'SONDA NASOGÁSTRICA N 18', 2, 13, 5, NULL, 'Disponible'),
(275, 'SONDA NELATON N 8', 2, 13, 5, NULL, 'Disponible'),
(276, 'SONDA NELATON N 10', 2, 13, 5, NULL, 'Disponible'),
(277, 'SONDA NELATON N 12', 2, 13, 5, NULL, 'Disponible'),
(278, 'SONDA NELATON N 14', 2, 13, 5, NULL, 'Disponible'),
(279, 'SONDA NELATON N 16', 2, 13, 5, NULL, 'Disponible'),
(280, 'SONDA NELATON N 18', 2, 13, 5, NULL, 'Disponible'),
(281, 'SUTURA CATGUT CRÓMICO 0 CON AGUJA', 2, 13, 12, NULL, 'Disponible'),
(282, 'SUTURA CATGUT CRÓMICO 2-0 CON AGUJA', 2, 13, 12, NULL, 'Disponible'),
(283, 'SUTURA CATGUT CRÓMICO 3-0 CON AGUJA', 2, 13, 12, NULL, 'Disponible'),
(284, 'SUTURA CATGUT SIMPLE 2-0 CON AGUJA', 2, 13, 12, NULL, 'Disponible'),
(285, 'SUTURA CATGUT SIMPLE 3-0 CON AGUJA', 2, 13, 12, NULL, 'Disponible'),
(286, 'SUTURA NYLON 2-0 CON AGUJA', 2, 13, 12, NULL, 'Disponible'),
(287, 'SUTURA NYLON 3-0 CON AGUJA', 2, 13, 12, NULL, 'Disponible'),
(288, 'SUTURA NYLON 4-0 CON AGUJA', 2, 13, 12, NULL, 'Disponible'),
(289, 'SUTURA NYLON 5-0 CON AGUJA', 2, 13, 12, NULL, 'Disponible'),
(290, 'SUTURA NYLON 6-0 CON AGUJA', 2, 13, 12, NULL, 'Disponible'),
(291, 'SUTURA SEDA 2-0 CON AGUJA', 2, 13, 12, NULL, 'Disponible'),
(292, 'SUTURA SEDA 3-0 CON AGUJA', 2, 13, 12, NULL, 'Disponible'),
(293, 'SUTURA SEDA 4-0 CON AGUJA', 2, 13, 12, NULL, 'Disponible'),
(294, 'SUTURA VICRYL 0 CON AGUJA', 2, 13, 12, NULL, 'Disponible'),
(295, 'SUTURA VICRYL 2-0 CON AGUJA', 2, 13, 12, NULL, 'Disponible'),
(296, 'SUTURA VICRYL 3-0 CON AGUJA', 2, 13, 12, NULL, 'Disponible'),
(297, 'SUTURA VICRYL 4-0 CON AGUJA', 2, 13, 12, NULL, 'Disponible'),
(298, 'TERMÓMETRO CLÍNICO DIGITAL', 2, 13, 2, NULL, 'Disponible'),
(299, 'TIJERA DE MAYO', 2, 13, 1, NULL, 'Disponible'),
(300, 'TORUNDA DE ALGODÓN CON ALCOHOL', 2, 15, 1, NULL, 'Disponible'),
(301, 'TRACTO O EXTENSIÓN PARA SUCCIÓN', 2, 13, 5, NULL, 'Disponible'),
(302, 'TUBO DE ASPIRACIÓN YANKAUER', 2, 13, 5, NULL, 'Disponible'),
(303, 'TUBO ENDOTRAQUEAL C/C N 3,0', 2, 13, 2, NULL, 'Disponible'),
(304, 'TUBO ENDOTRAQUEAL C/C N 3,5', 2, 13, 2, NULL, 'Disponible'),
(305, 'TUBO ENDOTRAQUEAL C/C N 4,0', 2, 13, 2, NULL, 'Disponible'),
(306, 'TUBO ENDOTRAQUEAL C/C N 4,5', 2, 13, 2, NULL, 'Disponible'),
(307, 'TUBO ENDOTRAQUEAL C/C N 5,0', 2, 13, 2, NULL, 'Disponible'),
(308, 'TUBO ENDOTRAQUEAL C/C N 5,5', 2, 13, 2, NULL, 'Disponible'),
(309, 'TUBO ENDOTRAQUEAL C/C N 6,0', 2, 13, 2, NULL, 'Disponible'),
(310, 'TUBO ENDOTRAQUEAL C/C N 6,5', 2, 13, 5, NULL, 'Disponible'),
(311, 'TUBO ENDOTRAQUEAL C/C N 7,0', 2, 13, 10, NULL, 'Disponible'),
(312, 'TUBO ENDOTRAQUEAL C/C N 7,5', 2, 13, 10, NULL, 'Disponible'),
(313, 'TUBO ENDOTRAQUEAL C/C N 8,0', 2, 13, 10, NULL, 'Disponible'),
(314, 'TUBO ENDOTRAQUEAL C/C N 8,5', 2, 13, 5, NULL, 'Disponible'),
(315, 'TUBO ENDOTRAQUEAL S/C N 2,5', 2, 13, 2, NULL, 'Disponible'),
(316, 'TUBO ENDOTRAQUEAL S/C N 3,0', 2, 13, 2, NULL, 'Disponible'),
(317, 'TUBO ENDOTRAQUEAL S/C N 3,5', 2, 13, 2, NULL, 'Disponible'),
(318, 'TUBO ENDOTRAQUEAL S/C N 4,0', 2, 13, 2, NULL, 'Disponible'),
(319, 'TUBO ENDOTRAQUEAL S/C N 4,5', 2, 13, 2, NULL, 'Disponible'),
(320, 'TUBO ENDOTRAQUEAL S/C N 5,0', 2, 13, 2, NULL, 'Disponible'),
(321, 'TUBO TORÁCICO DE TORACOSTOMÍA N 16', 2, 13, 2, NULL, 'Disponible'),
(322, 'TUBO TORÁCICO DE TORACOSTOMÍA N 20', 2, 13, 2, NULL, 'Disponible'),
(323, 'TUBO TORÁCICO DE TORACOSTOMÍA N 24', 2, 13, 2, NULL, 'Disponible'),
(324, 'TUBO TORÁCICO DE TORACOSTOMÍA N 28', 2, 13, 2, NULL, 'Disponible'),
(325, 'TUBO TORÁCICO DE TORACOSTOMÍA N 32', 2, 13, 2, NULL, 'Disponible'),
(326, 'TUBO TORÁCICO DE TORACOSTOMÍA N 36', 2, 13, 2, NULL, 'Disponible'),
(327, 'TUBOS DE ENSAYO TAPA ROJA', 2, 13, 100, NULL, 'Disponible'),
(328, 'TUBOS DE ENSAYO TAPA MORADA', 2, 13, 100, NULL, 'Disponible'),
(329, 'TUBOS DE ENSAYO TAPA AZUL', 2, 13, 100, NULL, 'Disponible'),
(330, 'INICIALIZADOR O GUÍA DE MARCAPASO', 2, 13, 1, NULL, 'Disponible'),
(331, 'ÍNTIMA O MARIPOSA DE INFUSÍON N 21 G', 2, 13, 10, NULL, 'Disponible'),
(332, 'ÍNTIMA O MARIPOSA DE INFUSÍON N 23 G', 2, 13, 10, NULL, 'Disponible'),
(333, 'ÍNTIMA O MARIPOSA DE INFUSÍON N 25 G', 2, 13, 10, NULL, 'Disponible'),
(334, 'ÍNTIMA O MARIPOSA DE INFUSÍON N 27 G', 2, 13, 10, NULL, 'Disponible'),
(335, 'JERINGA PEDIÁTRICA 1 CC DE INSULINA', 2, 13, 100, NULL, 'Disponible'),
(336, 'JERINGA DESECHABLE 3 CC C/A N 22', 2, 13, 100, NULL, 'Disponible'),
(337, 'JERINGA DESECHABLE 3 CC C/A N 23', 2, 13, 100, NULL, 'Disponible'),
(338, 'JERINGA DESECHABLE 5 CC C/A N 21', 2, 13, 100, NULL, 'Disponible'),
(339, 'JERINGA DESECHABLE 5 CC C/A N 22', 2, 13, 100, NULL, 'Disponible'),
(340, 'JERINGA DESECHABLE 10 CC C/A N 21', 2, 13, 100, NULL, 'Disponible'),
(341, 'JERINGA DESECHABLE 20 CC S/A', 2, 13, 50, NULL, 'Disponible'),
(342, 'JERINGA DESECHABLE 50 CC S/A PICO CATÉTER', 2, 13, 20, NULL, 'Disponible'),
(343, 'JERINGA DESECHABLE 60 CC S/A PICO CATÉTER', 2, 13, 20, NULL, 'Disponible'),
(344, 'LLAVE DE TRES VÍAS CON EXTENSIÓN', 2, 13, 50, NULL, 'Disponible'),
(345, 'LLAVE DE TRES VÍAS SIMPLE', 2, 13, 50, NULL, 'Disponible'),
(346, 'MANIJA PARA LÁMPARA CIALÍTICA', 2, 13, 1, NULL, 'Disponible'),
(347, 'MANGUERA DE SUCCIÓN CORRUGADA', 2, 12, 1, NULL, 'Disponible'),
(348, 'MARCAPASO TEMPORAL TRANSVENOSO CON CABLE', 2, 13, 1, NULL, 'Disponible'),
(349, 'MEDIDOR DE FLUJO EXPIRATORIO', 2, 13, 1, NULL, 'Disponible'),
(350, 'OBTURADOR DE SELLO DE AGUA', 2, 13, 2, NULL, 'Disponible'),
(351, 'SISTEMA DE DRENAJE TORÁCICO TIPO PLEUR EVAC', 2, 13, 2, NULL, 'Disponible'),
(352, 'SISTEMA DE SUCCIÓN CERRADO N 12', 2, 13, 1, NULL, 'Disponible'),
(353, 'SISTEMA DE SUCCIÓN CERRADO N 14', 2, 13, 1, NULL, 'Disponible'),
(354, 'SOLUCIÓN DESINFECTANTE ENZIMÁTICA', 2, 14, 1, NULL, 'Disponible'),
(355, 'SURGICEL MALLA HEMOSTÁTICA', 2, 13, 2, NULL, 'Disponible'),
(356, 'VENDA DE GASA 2 PULGADAS', 2, 13, 50, NULL, 'Disponible'),
(357, 'VENDA DE GASA 3 PULGADAS', 2, 13, 50, NULL, 'Disponible'),
(358, 'VENDA DE GASA 4 PULGADAS', 2, 13, 50, NULL, 'Disponible'),
(359, 'VENDA DE GASA 6 PULGADAS', 2, 13, 50, NULL, 'Disponible'),
(360, 'VENDA ELÁSTICA 2 PULGADAS', 2, 13, 20, NULL, 'Disponible'),
(361, 'VENDA ELÁSTICA 3 PULGADAS', 2, 13, 20, NULL, 'Disponible'),
(362, 'VENDA ELÁSTICA 4 PULGADAS', 2, 13, 20, NULL, 'Disponible'),
(363, 'VENDA ELÁSTICA 6 PULGADAS', 2, 13, 20, NULL, 'Disponible'),
(364, 'VENDA DE YESO 3 PULGADAS', 2, 13, 10, NULL, 'Disponible'),
(365, 'VENDA DE YESO 4 PULGADAS', 2, 13, 10, NULL, 'Disponible'),
(366, 'VENDA DE YESO 6 PULGADAS', 2, 13, 10, NULL, 'Disponible'),
(367, 'ACIDO ACETILSALICILICO', 3, 6, 150, NULL, 'Disponible'),
(368, 'ACIDO FOLICO 10MG', 3, 6, 1000, '2028-08-01', 'Disponible'),
(369, 'ACIDO FOLICO 5MG', 3, 6, 500, '2027-05-01', 'Disponible'),
(370, 'ACIDO VALPROICO 250 MG', 3, 6, 90, '2027-02-01', 'Disponible'),
(371, 'ACIDO VALPROICO 500 MG', 3, 6, 33, '2027-11-01', 'Disponible'),
(372, 'ALPRAZOLAN', 3, 6, 120, '2028-07-01', 'Disponible'),
(373, 'ATENOLOL', 3, 6, 380, '2027-07-01', 'Disponible'),
(374, 'ATORVASTATINA 20 MG', 3, 6, 140, '2027-07-01', 'Disponible'),
(375, 'AZITROMICINA 500 MG', 3, 6, 80, '2028-09-01', 'Disponible'),
(376, 'CARBAMACEPINA 200 MG', 3, 6, 210, '2027-01-01', 'Disponible'),
(377, 'CARVEDILOL 12,5 MG', 3, 6, 300, '2027-07-01', 'Disponible'),
(378, 'CEFADROXILO 500 MG', 3, 6, 100, '2027-03-01', 'Disponible'),
(379, 'CEFALEXINA', 3, 6, 48, '2026-09-01', 'Vencido'),
(380, 'CETIRIZINA 10 MG', 3, 6, 500, '2027-12-01', 'Disponible'),
(381, 'CIPROFLOXACINA 500 MG', 3, 6, 300, '2027-11-01', 'Disponible'),
(382, 'CLARITROMICINA 500 MG', 3, 6, 100, '2028-02-01', 'Disponible'),
(383, 'CLONACEPAN 2 MG', 3, 6, 180, '2027-07-01', 'Disponible'),
(384, 'CLOPIDOGREL 75 MG', 3, 6, 200, '2026-10-01', 'Por Vencer'),
(385, 'COMPLEJO B', 3, 6, 800, '2027-06-01', 'Disponible'),
(386, 'DEXAMETASONA 0,5 MG', 3, 6, 150, '2027-04-01', 'Disponible'),
(387, 'TEOFILINA', 3, 6, 30, '2026-08-01', 'Vencido'),
(388, 'CAPTOPRIL 25 MG', 3, 8, 590, '2024-04-01', 'Vencido'),
(389, 'DICLOFENAC SODICO 10 MG', 3, 8, 275, '2025-05-01', 'Vencido'),
(390, 'DICLOFENAC SODICO 50 MG', 3, 8, 5, '2025-05-01', 'Vencido'),
(391, 'DIGOXINA 0.25 G', 3, 8, 140, '2024-06-01', 'Vencido'),
(392, 'FEROBARBITAL 100 MG', 3, 8, 627, '2025-11-01', 'Vencido'),
(393, 'FLUOXETINA 20 MG', 3, 8, 70, '2025-05-01', 'Vencido'),
(394, 'GLIBEMCLAMIDA 5 MG', 3, 8, 630, '2025-05-01', 'Vencido'),
(395, 'HIDROCLOROTIAZIDA 25 MG', 3, 8, 102, '2025-10-01', 'Vencido'),
(396, 'LEVOFLOXACINA 500 MG', 3, 8, 322, '2026-01-01', 'Vencido'),
(397, 'METFORMINA 850 MG', 3, 8, 180, '2025-10-01', 'Vencido'),
(398, 'NIFEDIPINA 20 MG', 3, 8, 10, '2024-04-01', 'Vencido'),
(399, 'PREDNISOLONA 5 MG', 3, 8, 490, '2025-06-01', 'Vencido'),
(400, 'PREDNISOLONA 5 MG', 3, 8, 84, '2025-02-01', 'Vencido'),
(401, 'TETRACILINA', 3, 8, 18, '2025-06-01', 'Vencido'),
(402, 'FLUOXETINA 20 MG', 3, 8, 100, '2025-05-01', 'Vencido'),
(403, 'ERYTHROMYCIN ORAL', 3, 7, 20, '2025-06-01', 'Vencido'),
(404, 'ENALAPRIL 20 MG', 3, 6, 600, '2028-01-01', 'Disponible'),
(405, 'FOLICO ACIDO 5 MG', 3, 6, 400, '2027-08-01', 'Disponible'),
(406, 'FUROSEMIDA 40 MG', 3, 6, 350, '2027-11-01', 'Disponible'),
(407, 'IBUPROFENO 400 MG', 3, 6, 500, '2028-02-01', 'Disponible'),
(408, 'ISOSORBIDE DINITRATO 10 MG', 3, 6, 100, '2027-09-01', 'Disponible'),
(409, 'KETOPROFENO 100 MG', 3, 6, 250, '2027-06-01', 'Disponible'),
(410, 'LORATADINA 10 MG', 3, 6, 450, '2028-05-01', 'Disponible'),
(411, 'LOSARTAN POTASICO 50 MG', 3, 6, 800, '2028-03-01', 'Disponible'),
(412, 'METOCLOPRAMIDA 10 MG', 3, 6, 200, '2027-07-01', 'Disponible'),
(413, 'METOPROLOL 50 MG', 3, 6, 300, '2027-10-01', 'Disponible'),
(414, 'OMEPRAZOL 20 MG', 3, 6, 600, '2028-01-01', 'Disponible'),
(415, 'PARACETAMOL 500 MG', 3, 6, 1200, '2028-06-01', 'Disponible'),
(416, 'PRAVASTATINA 20 MG', 3, 6, 150, '2027-05-01', 'Disponible'),
(417, 'SULFATO FERROSO 200 MG', 3, 6, 900, '2028-04-01', 'Disponible'),
(418, 'VALPROATO DE SODIO 500 MG', 3, 6, 120, '2027-12-01', 'Disponible'),
(419, 'VERAPAMILO 80 MG', 3, 6, 220, '2027-08-01', 'Disponible'),
(420, 'ADHESIVO TRANSPORE', 4, 12, 0, NULL, 'Disponible'),
(421, 'TROPICAMIDE', 4, 2, 0, '2027-08-01', 'Disponible'),
(422, 'ACETOINA TRIAMCINOLONA', 4, 1, 14, NULL, 'Disponible'),
(423, 'AGUJA OFTALMICAS 23GX1', 4, 15, 0, NULL, 'Disponible'),
(424, 'AZUL TRIPANO', 4, 1, 178, '2027-04-01', 'Disponible'),
(425, 'CARBACHOL O MIOCHOL', 4, 1, 40, '2027-05-01', 'Disponible'),
(426, 'CARTUCHO DE LENTES', 4, 13, 59, '2027-11-01', 'Disponible'),
(427, 'CEFAZOLINA OFTALMICA 5%', 4, 2, 5, '2027-03-01', 'Disponible'),
(428, 'CIPROFLOXACINA GOTAS OFTALMICAS', 4, 2, 12, '2027-12-01', 'Disponible'),
(429, 'CLORANFENICOL OFTALMICO', 4, 2, 8, '2027-06-01', 'Disponible'),
(430, 'CICLOPLEJICO GOTAS', 4, 2, 10, '2027-09-01', 'Disponible'),
(431, 'DEXAMETASONA OFTÁLMICA', 4, 2, 25, '2028-01-01', 'Disponible'),
(432, 'DICLOFENAC OFTÁLMICO', 4, 2, 18, '2027-07-01', 'Disponible'),
(433, 'DORZOLAMIDA GOTAS', 4, 2, 15, '2027-10-01', 'Disponible'),
(434, 'FENILEFRINA GOTAS OFTÁLMICAS', 4, 2, 20, '2027-11-01', 'Disponible'),
(435, 'FLUORESCEINA TIRAS', 4, 15, 2, '2028-05-01', 'Disponible'),
(436, 'GENTAMICINA OFTALMICA', 4, 2, 30, '2027-08-01', 'Disponible'),
(437, 'HIPROMELOSA LAGRIMAS ARTIFICIALES', 4, 2, 45, '2028-02-01', 'Disponible'),
(438, 'LATANOPROST GOTAS', 4, 2, 14, '2027-09-01', 'Disponible'),
(439, 'MITOMIXINA 20 MG', 4, 2, 1, '2026-07-01', 'Vencido'),
(440, 'TIMOLOL 0,5% GOTAS', 4, 2, 35, '2028-03-01', 'Disponible'),
(441, 'TOBRAMICINA OFTALMICA', 4, 2, 22, '2027-10-01', 'Disponible'),
(442, 'TOBRAMICINA CON DEXAMETASONA', 4, 2, 19, '2027-11-01', 'Disponible'),
(443, 'VISCOELASTICO OFTALMICO', 4, 1, 85, '2028-01-01', 'Disponible'),
(444, 'DETERGENTE ENZIMATICO', 2, 14, 0, NULL, 'Disponible'),
(445, 'YODO', 2, 14, 0, NULL, 'Disponible'),
(446, 'CLORO', 2, 14, 0, NULL, 'Disponible'),
(447, 'FORMOL', 2, 14, 0, NULL, 'Disponible'),
(448, 'GEL PARA ECO', 2, 14, 0, NULL, 'Disponible'),
(449, 'GERDEX', 2, 14, 0, NULL, 'Disponible'),
(450, 'AGUA OXIGENADA', 2, 14, 0, NULL, 'Disponible'),
(451, 'ALCOHOL', 2, 14, 0, NULL, 'Disponible'),
(452, 'CLORHEXIDINA', 2, 14, 0, NULL, 'Disponible'),
(453, 'SOLUCION 0,9', 1, 4, 0, NULL, 'Disponible'),
(454, 'GLUCOSA', 1, 4, 0, NULL, 'Disponible'),
(455, 'RINGER', 1, 4, 0, NULL, 'Disponible'),
(456, 'DEXTROSA', 1, 4, 0, NULL, 'Disponible'),
(457, 'AGUA OXIGENADA', 1, 4, 0, NULL, 'Disponible'),
(458, 'ALCOHOL', 1, 4, 0, NULL, 'Disponible'),
(459, 'SILLA DE RUEDAS DE ADULTOS', 5, 13, 0, NULL, 'Disponible'),
(460, 'SILLA DE RUEDAS DE NIÑOS', 5, 13, 0, NULL, 'Disponible'),
(461, 'BASTON DE 1 PUNTA', 5, 13, 0, NULL, 'Disponible'),
(462, 'BASTON DE 4 PUNTA', 5, 13, 0, NULL, 'Disponible'),
(463, 'ANDADERAS', 5, 13, 0, NULL, 'Disponible'),
(464, 'MULETAS', 5, 13, 0, NULL, 'Disponible'),
(465, 'TENSIOMETRO', 5, 13, 0, NULL, 'Disponible'),
(466, 'GLUCOMETRO', 5, 13, 0, NULL, 'Disponible'),
(467, 'LENTE INTRAOCULAR Nº 1.0', 4, 8, 1, NULL, 'Disponible'),
(468, 'LENTE INTRAOCULAR Nº 9.0', 4, 8, 2, NULL, 'Disponible'),
(469, 'LENTE INTRAOCULAR N 10.0', 4, 8, 1, NULL, 'Disponible'),
(470, 'LENTE INTRAOCULAR N 10.50', 4, 8, 3, NULL, 'Disponible'),
(471, 'LENTE INTRAOCULAR N 11.00', 4, 8, 4, NULL, 'Disponible'),
(472, 'LENTE INTRAOCULAR N 11.50', 4, 8, 4, NULL, 'Disponible'),
(473, 'LENTE INTRAOCULAR N 13.00', 4, 8, 9, NULL, 'Disponible'),
(474, 'LENTE INTRAOCULAR N 14.00', 4, 8, 4, NULL, 'Disponible'),
(475, 'LENTE INTRAOCULAR N 14.50', 4, 8, 5, NULL, 'Disponible'),
(476, 'LENTE INTRAOCULAR N 15.00', 4, 8, 3, NULL, 'Disponible'),
(477, 'LENTE INTRAOCULAR N 15.50', 4, 8, 4, NULL, 'Disponible'),
(478, 'LENTE INTRAOCULAR N 16.00', 4, 8, 5, NULL, 'Disponible'),
(479, 'LENTE INTRAOCULAR N 16.50', 4, 8, 5, NULL, 'Disponible'),
(480, 'LENTE INTRAOCULAR N 17.00', 4, 8, 6, NULL, 'Disponible'),
(481, 'LENTE INTRAOCULAR N 17.50', 4, 8, 11, NULL, 'Disponible'),
(482, 'LENTE INTRAOCULAR N 18.00', 4, 8, 12, NULL, 'Disponible'),
(483, 'LENTE INTRAOCULAR N 18.50', 4, 8, 4, NULL, 'Disponible'),
(484, 'LENTE INTRAOCULAR N 19.00', 4, 8, 4, NULL, 'Disponible'),
(485, 'LENTE INTRAOCULAR N 19.50', 4, 8, 4, NULL, 'Disponible'),
(486, 'LENTE INTRAOCULAR N 20.00', 4, 8, 4, NULL, 'Disponible'),
(487, 'LENTE INTRAOCULAR N 20.50', 4, 8, 7, NULL, 'Disponible'),
(488, 'LENTE INTRAOCULAR Nº 21.0', 4, 8, 7, NULL, 'Disponible'),
(489, 'LENTE INTRAOCULAR Nº 21.5', 4, 8, 4, NULL, 'Disponible'),
(490, 'LENTE INTRAOCULAR N 22.00', 4, 8, 0, NULL, 'Disponible'),
(491, 'LENTE INTRAOCULAR N 22.50', 4, 8, 10, NULL, 'Disponible'),
(492, 'LENTE INTRAOCULAR N 23.00', 4, 8, 9, NULL, 'Disponible'),
(493, 'LENTE INTRAOCULAR N 23.50', 4, 8, 12, NULL, 'Disponible'),
(494, 'LENTE INTRAOCULAR N 24.00', 4, 8, 9, NULL, 'Disponible'),
(495, 'LENTE INTRAOCULAR N 24.50', 4, 8, 10, NULL, 'Disponible'),
(496, 'LENTE INTRAOCULAR N 25.00', 4, 8, 6, NULL, 'Disponible'),
(497, 'LENTE INTRAOCULAR N 25.50', 4, 8, 18, NULL, 'Disponible'),
(498, 'LENTE INTRAOCULAR N 26.00', 4, 8, 6, NULL, 'Disponible'),
(499, 'LENTE INTRAOCULAR N 26.50', 4, 8, 7, NULL, 'Disponible'),
(500, 'LENTE INTRAOCULAR N 27.00', 4, 8, 8, NULL, 'Disponible'),
(501, 'LENTE INTRAOCULAR N 27.50', 4, 8, 4, NULL, 'Disponible'),
(502, 'LENTE INTRAOCULAR N 28.00', 4, 8, 2, NULL, 'Disponible'),
(503, 'LENTE INTRAOCULAR N 28.50', 4, 8, 7, NULL, 'Disponible'),
(504, 'LENTE INTRAOCULAR N 29.00', 4, 8, 0, NULL, 'Disponible'),
(505, 'LENTE INTRAOCULAR N 29.50', 4, 8, 5, NULL, 'Disponible'),
(506, 'LENTE INTRAOCULAR Nº 1.0', 4, 8, 1, NULL, 'Disponible'),
(507, 'LENTE INTRAOCULAR Nº 9.0', 4, 8, 2, NULL, 'Disponible'),
(508, 'LENTE INTRAOCULAR N 10.0', 4, 8, 1, NULL, 'Disponible'),
(509, 'LENTE INTRAOCULAR N 10.50', 4, 8, 3, NULL, 'Disponible'),
(510, 'LENTE INTRAOCULAR N 11.00', 4, 8, 4, NULL, 'Disponible'),
(511, 'LENTE INTRAOCULAR N 11.50', 4, 8, 4, NULL, 'Disponible'),
(512, 'LENTE INTRAOCULAR N 13.00', 4, 8, 9, NULL, 'Disponible'),
(513, 'LENTE INTRAOCULAR N 14.00', 4, 8, 4, NULL, 'Disponible'),
(514, 'LENTE INTRAOCULAR N 14.50', 4, 8, 5, NULL, 'Disponible'),
(515, 'LENTE INTRAOCULAR N 15.00', 4, 8, 3, NULL, 'Disponible'),
(516, 'LENTE INTRAOCULAR N 15.50', 4, 8, 4, NULL, 'Disponible'),
(517, 'LENTE INTRAOCULAR N 16.00', 4, 8, 5, NULL, 'Disponible'),
(518, 'LENTE INTRAOCULAR N 16.50', 4, 8, 5, NULL, 'Disponible'),
(519, 'LENTE INTRAOCULAR N 17.00', 4, 8, 6, NULL, 'Disponible'),
(520, 'LENTE INTRAOCULAR N 17.50', 4, 8, 11, NULL, 'Disponible'),
(521, 'LENTE INTRAOCULAR N 18.00', 4, 8, 12, NULL, 'Disponible'),
(522, 'LENTE INTRAOCULAR N 18.50', 4, 8, 4, NULL, 'Disponible'),
(523, 'LENTE INTRAOCULAR N 19.00', 4, 8, 4, NULL, 'Disponible'),
(524, 'LENTE INTRAOCULAR N 19.50', 4, 8, 4, NULL, 'Disponible'),
(525, 'LENTE INTRAOCULAR N 20.00', 4, 8, 4, NULL, 'Disponible'),
(526, 'LENTE INTRAOCULAR N 20.50', 4, 8, 7, NULL, 'Disponible'),
(527, 'LENTE INTRAOCULAR Nº 21.0', 4, 8, 7, NULL, 'Disponible'),
(528, 'LENTE INTRAOCULAR Nº 21.5', 4, 8, 4, NULL, 'Disponible'),
(529, 'LENTE INTRAOCULAR N 22.00', 4, 8, 0, NULL, 'Disponible'),
(530, 'LENTE INTRAOCULAR N 22.50', 4, 8, 10, NULL, 'Disponible'),
(531, 'LENTE INTRAOCULAR N 23.00', 4, 8, 9, NULL, 'Disponible'),
(532, 'LENTE INTRAOCULAR N 23.50', 4, 8, 12, NULL, 'Disponible'),
(533, 'LENTE INTRAOCULAR N 24.00', 4, 8, 9, NULL, 'Disponible'),
(534, 'LENTE INTRAOCULAR N 24.50', 4, 8, 10, NULL, 'Disponible'),
(535, 'LENTE INTRAOCULAR N 25.00', 4, 8, 6, NULL, 'Disponible'),
(536, 'LENTE INTRAOCULAR N 25.50', 4, 8, 18, NULL, 'Disponible'),
(537, 'LENTE INTRAOCULAR N 26.00', 4, 8, 6, NULL, 'Disponible'),
(538, 'LENTE INTRAOCULAR N 26.50', 4, 8, 7, NULL, 'Disponible'),
(539, 'LENTE INTRAOCULAR N 27.00', 4, 8, 8, NULL, 'Disponible'),
(540, 'LENTE INTRAOCULAR N 27.50', 4, 8, 4, NULL, 'Disponible'),
(541, 'LENTE INTRAOCULAR N 28.00', 4, 8, 2, NULL, 'Disponible'),
(542, 'LENTE INTRAOCULAR N 28.50', 4, 8, 7, NULL, 'Disponible'),
(543, 'LENTE INTRAOCULAR N 29.00', 4, 8, 0, NULL, 'Disponible'),
(544, 'LENTE INTRAOCULAR N 29.50', 4, 8, 5, NULL, 'Disponible');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lentes_intraoculares`
--

CREATE TABLE `lentes_intraoculares` (
  `id_lente` bigint(20) UNSIGNED NOT NULL,
  `id_producto` bigint(20) UNSIGNED NOT NULL,
  `dioptria` decimal(4,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `lentes_intraoculares`
--

INSERT INTO `lentes_intraoculares` (`id_lente`, `id_producto`, `dioptria`) VALUES
(1, 467, 1.00),
(2, 468, 9.00),
(3, 469, 10.00),
(4, 470, 10.50),
(5, 471, 11.00),
(6, 472, 11.50),
(7, 473, 13.00),
(8, 474, 14.00),
(9, 475, 14.50),
(10, 476, 15.00),
(11, 477, 15.50),
(12, 478, 16.00),
(13, 479, 16.50),
(14, 480, 17.00),
(15, 481, 17.50),
(16, 482, 18.00),
(17, 483, 18.50),
(18, 484, 19.00),
(19, 485, 19.50),
(20, 486, 20.00),
(21, 487, 20.50),
(22, 488, 21.00),
(23, 489, 21.50),
(24, 490, 22.00),
(25, 491, 22.50),
(26, 492, 23.00),
(27, 493, 23.50),
(28, 494, 24.00),
(29, 495, 24.50),
(30, 496, 25.00),
(31, 497, 25.50),
(32, 498, 26.00),
(33, 499, 26.50),
(34, 500, 27.00),
(35, 501, 27.50),
(36, 502, 28.00),
(37, 503, 28.50),
(38, 504, 29.00),
(39, 505, 29.50),
(40, 506, 1.00),
(41, 507, 9.00),
(42, 508, 10.00),
(43, 509, 10.50),
(44, 510, 11.00),
(45, 511, 11.50),
(46, 512, 13.00),
(47, 513, 14.00),
(48, 514, 14.50),
(49, 515, 15.00),
(50, 516, 15.50),
(51, 517, 16.00),
(52, 518, 16.50),
(53, 519, 17.00),
(54, 520, 17.50),
(55, 521, 18.00),
(56, 522, 18.50),
(57, 523, 19.00),
(58, 524, 19.50),
(59, 525, 20.00),
(60, 526, 20.50),
(61, 527, 21.00),
(62, 528, 21.50),
(63, 529, 22.00),
(64, 530, 22.50),
(65, 531, 23.00),
(66, 532, 23.50),
(67, 533, 24.00),
(68, 534, 24.50),
(69, 535, 25.00),
(70, 536, 25.50),
(71, 537, 26.00),
(72, 538, 26.50),
(73, 539, 27.00),
(74, 540, 27.50),
(75, 541, 28.00),
(76, 542, 28.50),
(77, 543, 29.00),
(78, 544, 29.50);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presentaciones`
--

CREATE TABLE `presentaciones` (
  `id_presentacion` bigint(20) UNSIGNED NOT NULL,
  `nombre_presentacion` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `presentaciones`
--

INSERT INTO `presentaciones` (`id_presentacion`, `nombre_presentacion`) VALUES
(1, 'ampollas'),
(6, 'blister'),
(15, 'caja'),
(3, 'cremas'),
(8, 'empaque'),
(4, 'frasco'),
(14, 'galones'),
(18, 'gel'),
(2, 'gotas'),
(10, 'inhalador'),
(11, 'jarabe'),
(17, 'kit'),
(12, 'rollos'),
(16, 'sobres'),
(5, 'solucion'),
(7, 'supositorio'),
(9, 'suspension'),
(13, 'unidades');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `nombre_categoria` (`nombre_categoria`);

--
-- Indices de la tabla `inventario_general`
--
ALTER TABLE `inventario_general`
  ADD PRIMARY KEY (`id_producto`),
  ADD KEY `id_categoria` (`id_categoria`),
  ADD KEY `id_presentacion` (`id_presentacion`);

--
-- Indices de la tabla `lentes_intraoculares`
--
ALTER TABLE `lentes_intraoculares`
  ADD PRIMARY KEY (`id_lente`),
  ADD UNIQUE KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `presentaciones`
--
ALTER TABLE `presentaciones`
  ADD PRIMARY KEY (`id_presentacion`),
  ADD UNIQUE KEY `nombre_presentacion` (`nombre_presentacion`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `inventario_general`
--
ALTER TABLE `inventario_general`
  MODIFY `id_producto` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=545;

--
-- AUTO_INCREMENT de la tabla `lentes_intraoculares`
--
ALTER TABLE `lentes_intraoculares`
  MODIFY `id_lente` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT de la tabla `presentaciones`
--
ALTER TABLE `presentaciones`
  MODIFY `id_presentacion` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `inventario_general`
--
ALTER TABLE `inventario_general`
  ADD CONSTRAINT `fk_inv_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inv_presentacion` FOREIGN KEY (`id_presentacion`) REFERENCES `presentaciones` (`id_presentacion`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `lentes_intraoculares`
--
ALTER TABLE `lentes_intraoculares`
  ADD CONSTRAINT `fk_lentes_producto` FOREIGN KEY (`id_producto`) REFERENCES `inventario_general` (`id_producto`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
