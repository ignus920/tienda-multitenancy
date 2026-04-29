-- Tablas adicionales para el módulo de recepción de importaciones

-- Tabla para justificaciones de diferencias
CREATE TABLE IF NOT EXISTS `i_justificaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_importacion` int(11) NOT NULL,
  `justificacion` text NOT NULL,
  `idusuario` int(11) NOT NULL,
  `fecha_reg` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_importacion` (`id_importacion`),
  KEY `idusuario` (`idusuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla para novedades de productos
CREATE TABLE IF NOT EXISTS `i_novedades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_importacion` int(11) NOT NULL,
  `tipo_novedad` varchar(50) NOT NULL,
  `descripcion` text NOT NULL,
  `idusuario` int(11) NOT NULL,
  `fecha_reg` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_importacion` (`id_importacion`),
  KEY `idusuario` (`idusuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla para logs de actividades de recepción
CREATE TABLE IF NOT EXISTS `i_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_envio` int(11) DEFAULT NULL,
  `id_importacion` int(11) DEFAULT NULL,
  `accion` varchar(255) NOT NULL,
  `idusuario` int(11) NOT NULL,
  `fecha_reg` datetime NOT NULL DEFAULT current_timestamp(),
  `datos_anteriores` text DEFAULT NULL,
  `datos_nuevos` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_envio` (`id_envio`),
  KEY `id_importacion` (`id_importacion`),
  KEY `idusuario` (`idusuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insertar datos iniciales en i_estados si no existen
INSERT IGNORE INTO `i_estados` (`id`, `nombre`, `name`, `en_proceso`, `funcion`, `proveedor`, `edicion`) VALUES

(10, 'Cancelado', 'Cancelled', 'no', 'no', 'no', 'no');

-- Comentarios para las tablas existentes
COMMENT ON TABLE i_importacion IS 'Tabla principal de productos de importación';
COMMENT ON TABLE i_envios IS 'Tabla de envíos/shipments';
COMMENT ON TABLE i_picking IS 'Tabla de packings por envío';
COMMENT ON TABLE i_estados IS 'Tabla de estados del proceso de importación';

-- Comentarios para las nuevas tablas
COMMENT ON TABLE i_justificaciones IS 'Justificaciones para diferencias en cantidades recibidas';
COMMENT ON TABLE i_novedades IS 'Novedades encontradas durante la recepción';
COMMENT ON TABLE i_logs IS 'Log de actividades del proceso de recepción';