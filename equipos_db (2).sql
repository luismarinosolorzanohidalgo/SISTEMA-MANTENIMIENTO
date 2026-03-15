-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 23-12-2025 a las 19:33:59
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
-- Base de datos: `equipos_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividades_mantenimiento`
--

CREATE TABLE `actividades_mantenimiento` (
  `id` int(11) NOT NULL,
  `id_equipo` int(11) NOT NULL,
  `actividad` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_programada` date DEFAULT NULL,
  `fecha_asignacion` date DEFAULT NULL,
  `fecha_realizada` date DEFAULT NULL,
  `estado` enum('INICIADO','EN_PROCESO','TERMINADO') DEFAULT 'INICIADO',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria_intervalos`
--

CREATE TABLE `categoria_intervalos` (
  `id` int(11) NOT NULL,
  `categoria` varchar(100) NOT NULL,
  `intervalo_meses` int(11) NOT NULL,
  `tipo_mantenimiento` enum('Preventivo','Correctivo') DEFAULT 'Preventivo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categoria_intervalos`
--

INSERT INTO `categoria_intervalos` (`id`, `categoria`, `intervalo_meses`, `tipo_mantenimiento`) VALUES
(1, 'PC', 6, 'Preventivo'),
(2, 'Laptop', 4, 'Preventivo'),
(3, 'Impresora', 3, 'Preventivo'),
(4, 'Router', 6, 'Preventivo'),
(5, 'Switch', 12, 'Preventivo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipos`
--

CREATE TABLE `equipos` (
  `id_equipo` int(11) NOT NULL,
  `codigo_patrimonial` varchar(50) DEFAULT NULL,
  `nombre_equipo` varchar(150) NOT NULL,
  `categoria` varchar(50) NOT NULL,
  `marca` varchar(100) DEFAULT NULL,
  `modelo` varchar(100) DEFAULT NULL,
  `numero_serie` varchar(100) DEFAULT NULL,
  `estado` enum('Operativo','En Reparación','Dado de Baja') DEFAULT 'Operativo',
  `ubicacion` varchar(150) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultimo_mantenimiento` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `equipos`
--

INSERT INTO `equipos` (`id_equipo`, `codigo_patrimonial`, `nombre_equipo`, `categoria`, `marca`, `modelo`, `numero_serie`, `estado`, `ubicacion`, `fecha_registro`, `ultimo_mantenimiento`) VALUES
(1, NULL, 'Laptop Administrativa', 'Laptop', 'Lenovo', 'ThinkPad T480', 'SN-LNV-48291A', 'Operativo', 'Oficina Central', '2025-11-28 14:48:56', '2025-12-03'),
(2, NULL, 'Laptop Contabilidad', 'Laptop', 'HP', 'Pavilion 15', 'SN-HPV-59302B', 'En Reparación', 'Área de Contabilidad', '2025-11-28 14:48:56', NULL),
(3, NULL, 'PC Escritorio Caja 1', 'Pc', 'Dell', 'OptiPlex 3080', 'SN-DLL-3080X1', 'Operativo', 'Caja 1', '2025-11-28 14:48:56', NULL),
(4, NULL, 'Impresora Principal', 'Impresora', 'Epson', 'L3110', 'SN-EPS-3110C9', 'Operativo', 'Recepción', '2025-11-28 14:48:56', '2025-12-03'),
(5, NULL, 'Monitor Sala Reuniones', 'Monitor', 'Samsung', 'Curve 24\"', 'SN-SMG-24CRV', 'Operativo', 'Sala de Reuniones', '2025-11-28 14:48:56', NULL),
(6, NULL, 'Router Principal', 'Router', 'TP-Link', 'Archer C6', 'SN-TPL-C6AC54', 'Operativo', 'Cuarto de Servidores', '2025-11-28 14:48:56', NULL),
(7, NULL, 'Laptop Soporte Técnico', 'Laptop', 'Acer', 'Aspire 5', 'SN-ACR-A515G2', 'Operativo', 'Soporte Técnico', '2025-11-28 14:48:56', NULL),
(8, NULL, 'PC Escritorio Gestión', 'Pc', 'HP', 'EliteDesk 800', 'SN-HP-800ED12', 'Operativo', 'Gerencia', '2025-11-28 14:48:56', NULL),
(9, NULL, 'Impresora Contabilidad', 'Impresora', 'Brother', 'HL-L2320D', 'SN-BTH-2320D7', 'En Reparación', 'Contabilidad', '2025-11-28 14:48:56', NULL),
(10, NULL, 'Monitor Administrativo', 'Monitor', 'LG', 'UltraWide 29\"', 'SN-LG-UW29X1', 'Operativo', 'Administración', '2025-11-28 14:48:56', NULL),
(11, NULL, 'Switch de Red', 'Switch', 'Cisco', 'SG350-28', 'SN-CSC-SG35028', 'Operativo', 'Cuarto de Servidores', '2025-11-28 14:48:56', NULL),
(12, NULL, 'Laptop del Director', 'Laptop', 'Dell', 'Latitude 5400', 'SN-DLL-5400Q9', 'Operativo', 'Dirección', '2025-11-28 14:48:56', NULL),
(13, NULL, 'Proyector Sala 2', 'Proyector', 'BenQ', 'MS550', 'SN-BNQ-MS550A2', 'Operativo', 'Sala de Conferencias', '2025-11-28 14:48:56', NULL),
(14, NULL, 'Tablet Almacén', 'Tablet', 'Samsung', 'Galaxy Tab A7', 'SN-SMG-TABA7X1', 'Operativo', 'Almacén', '2025-11-28 14:48:56', NULL),
(15, NULL, 'CPU Desarrollo', 'Pc', 'Custom', 'Ryzen 5 3600', 'SN-CSTM-R53600X', 'Operativo', 'Área de Sistemas', '2025-11-28 14:48:56', '2025-12-03'),
(16, NULL, 'Laptop Marketing', 'Laptop', 'Apple', 'MacBook Air M1', 'SN-APL-M1A21', 'Operativo', 'Marketing', '2025-11-28 14:48:56', NULL),
(17, NULL, 'PC Escritorio Secretaria', 'Pc', 'Lenovo', 'ThinkCentre M720', 'SN-LNV-M720S5', 'Operativo', 'Secretaría', '2025-11-28 14:48:56', NULL),
(18, NULL, 'Monitor Recepción', 'Pc', 'HP', '24mh FHD', 'SN-HP-24MH34', 'Operativo', 'Recepción', '2025-11-28 14:48:56', NULL),
(19, NULL, 'Router Secundario', 'Router', 'Huawei', 'AX3 WiFi 6', 'SN-HW-AX3G91', 'Operativo', 'Área Administrativa', '2025-11-28 14:48:56', NULL),
(20, NULL, 'Laptop Practicante', 'Laptop', 'Asus', 'VivoBook 15', 'SN-ASUS-VB15X3', 'En Reparación', 'Soporte Técnico', '2025-11-28 14:48:56', '2025-12-03'),
(22, NULL, 'UPS', 'Otro', 'FORZA', 'LITE', 'UP1234233', 'Operativo', 'LABORATORIO 2', '2025-12-03 02:00:31', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mantenimientos`
--

CREATE TABLE `mantenimientos` (
  `id_mantenimiento` int(11) NOT NULL,
  `id_equipo` int(11) NOT NULL,
  `tipo` enum('Preventivo','Correctivo') DEFAULT 'Preventivo',
  `descripcion` text NOT NULL,
  `responsable` varchar(120) DEFAULT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `documento` varchar(255) DEFAULT NULL,
  `estado` enum('En Proceso','Completado') DEFAULT 'En Proceso',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mantenimientos`
--

INSERT INTO `mantenimientos` (`id_mantenimiento`, `id_equipo`, `tipo`, `descripcion`, `responsable`, `fecha_inicio`, `fecha_fin`, `documento`, `estado`, `fecha_registro`) VALUES
(1, 15, 'Correctivo', 'Promoción fin de semanas', 'roberts', '2025-12-03', '2025-12-03', NULL, 'Completado', '2025-12-03 00:07:30'),
(4, 4, 'Preventivo', 'Mantenimiento programado desde cálculo automático', 'MIGUEL', '2025-12-02', '2025-12-03', NULL, 'Completado', '2025-12-03 04:59:15'),
(5, 15, 'Correctivo', 'Promoción Black Friday', 'Jairo', '2025-12-03', '2025-12-03', NULL, 'Completado', '2025-12-03 05:28:00'),
(7, 9, 'Preventivo', 'Mantenimiento programado desde cálculo automático', 'MIGUEL', '2026-02-28', NULL, NULL, 'Completado', '2025-12-16 17:57:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materiales_usados`
--

CREATE TABLE `materiales_usados` (
  `id` int(11) NOT NULL,
  `id_actividad` int(11) NOT NULL,
  `material` varchar(200) DEFAULT NULL,
  `cantidad` varchar(50) DEFAULT NULL,
  `creado_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordenes_trabajo`
--

CREATE TABLE `ordenes_trabajo` (
  `id` int(11) NOT NULL,
  `id_actividad` int(11) NOT NULL,
  `tecnico` varchar(150) DEFAULT NULL,
  `fecha_asignacion` date DEFAULT NULL,
  `estado` enum('INICIADO','EN_PROCESO','TERMINADO') DEFAULT 'INICIADO',
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rotaciones`
--

CREATE TABLE `rotaciones` (
  `id_rotacion` int(11) NOT NULL,
  `id_equipo` int(11) NOT NULL,
  `origen` varchar(150) NOT NULL,
  `destino` varchar(150) NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `fecha_origen` date DEFAULT NULL,
  `fecha_destino` date NOT NULL,
  `responsable` varchar(150) DEFAULT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rotaciones`
--

INSERT INTO `rotaciones` (`id_rotacion`, `id_equipo`, `origen`, `destino`, `motivo`, `fecha_origen`, `fecha_destino`, `responsable`, `fecha_registro`) VALUES
(1, 1, 'Oficina 1', 'Laboratorio', 'Reubicación por mantenimiento', '2024-01-10', '2024-01-12', 'Carlos Pérez', '2025-12-02 17:39:55'),
(2, 1, 'Laboratorio', 'Oficina 3', 'Cambio de área', '2024-05-02', '2024-05-03', 'María López', '2025-12-02 17:39:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ubicaciones`
--

CREATE TABLE `ubicaciones` (
  `id_ubicacion` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ubicaciones`
--

INSERT INTO `ubicaciones` (`id_ubicacion`, `nombre`) VALUES
(1, 'Laboratorio TIC 01'),
(2, 'Laboratorio TIC 02'),
(3, 'Laboratorio Enfermería 01'),
(4, 'Laboratorio Enfermería 02'),
(5, 'Aula Contabilidad I'),
(6, 'Aula Contabilidad II'),
(7, 'Aula Contabilidad III'),
(8, 'Aula DSI I'),
(9, 'Aula DSI II'),
(10, 'Aula DSI III'),
(11, 'Aula Enfermería I'),
(12, 'Aula Enfermería II'),
(13, 'Aula Enfermería III'),
(14, 'Patrimonio'),
(15, 'Dirección'),
(16, 'Coordinación Contabilidad'),
(17, 'Coordinación DSI'),
(18, 'Coordinación Enfermería'),
(19, 'Auditorio');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('Administrador','tecnico','usuario') NOT NULL DEFAULT 'usuario'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `usuario`, `password`, `rol`) VALUES
(1, 'Luis Solorzano', 'admin', '$2y$10$xMB8m.FLhPBlUj5RKaq5fuy7/Zgixz0LBUTdlyZiO4KIXFWFnEyTq', 'Administrador'),
(5, 'Robert Alexis', 'Ingeniero', '$2y$10$k8D/rBXxdaw2MXlPyQfUjeYXfIjq1znxwuDS4v0yFGAZUEU23Qk3u', 'Administrador'),
(6, 'Luis', 'Tecnico1', '$2y$10$NASrHaOzB4Ab0myEySP5KureyEPhvGTf/F7rTQALKZ84FcLBbWcD.', 'tecnico');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `actividades_mantenimiento`
--
ALTER TABLE `actividades_mantenimiento`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `categoria_intervalos`
--
ALTER TABLE `categoria_intervalos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `equipos`
--
ALTER TABLE `equipos`
  ADD PRIMARY KEY (`id_equipo`),
  ADD UNIQUE KEY `numero_serie` (`numero_serie`);

--
-- Indices de la tabla `mantenimientos`
--
ALTER TABLE `mantenimientos`
  ADD PRIMARY KEY (`id_mantenimiento`),
  ADD KEY `id_equipo` (`id_equipo`);

--
-- Indices de la tabla `materiales_usados`
--
ALTER TABLE `materiales_usados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_actividad` (`id_actividad`);

--
-- Indices de la tabla `ordenes_trabajo`
--
ALTER TABLE `ordenes_trabajo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_actividad` (`id_actividad`);

--
-- Indices de la tabla `rotaciones`
--
ALTER TABLE `rotaciones`
  ADD PRIMARY KEY (`id_rotacion`),
  ADD KEY `id_equipo` (`id_equipo`);

--
-- Indices de la tabla `ubicaciones`
--
ALTER TABLE `ubicaciones`
  ADD PRIMARY KEY (`id_ubicacion`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `actividades_mantenimiento`
--
ALTER TABLE `actividades_mantenimiento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `categoria_intervalos`
--
ALTER TABLE `categoria_intervalos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `equipos`
--
ALTER TABLE `equipos`
  MODIFY `id_equipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `mantenimientos`
--
ALTER TABLE `mantenimientos`
  MODIFY `id_mantenimiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `materiales_usados`
--
ALTER TABLE `materiales_usados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ordenes_trabajo`
--
ALTER TABLE `ordenes_trabajo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `rotaciones`
--
ALTER TABLE `rotaciones`
  MODIFY `id_rotacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `ubicaciones`
--
ALTER TABLE `ubicaciones`
  MODIFY `id_ubicacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `mantenimientos`
--
ALTER TABLE `mantenimientos`
  ADD CONSTRAINT `mantenimientos_ibfk_1` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id_equipo`);

--
-- Filtros para la tabla `materiales_usados`
--
ALTER TABLE `materiales_usados`
  ADD CONSTRAINT `materiales_usados_ibfk_1` FOREIGN KEY (`id_actividad`) REFERENCES `actividades_mantenimiento` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `ordenes_trabajo`
--
ALTER TABLE `ordenes_trabajo`
  ADD CONSTRAINT `ordenes_trabajo_ibfk_1` FOREIGN KEY (`id_actividad`) REFERENCES `actividades_mantenimiento` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `rotaciones`
--
ALTER TABLE `rotaciones`
  ADD CONSTRAINT `rotaciones_ibfk_1` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id_equipo`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
