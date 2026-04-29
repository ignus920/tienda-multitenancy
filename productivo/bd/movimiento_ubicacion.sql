-- Tabla para registrar movimientos de productos en vitrina
CREATE TABLE `movimiento_ubicacion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_producto` int(11) NOT NULL,
  `codigo_producto` varchar(255) NOT NULL,
  `descripcion_producto` varchar(255) NOT NULL,
  `tipo_movimiento` enum('ENTRADA','SALIDA') NOT NULL,
  `cantidad` float NOT NULL,
  `ubicacion` varchar(50) NOT NULL DEFAULT 'Vitrina',
  `fecha_movimiento` timestamp NOT NULL DEFAULT current_timestamp(),
  `usuario_id` int(11) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `saldo_anterior` float NOT NULL DEFAULT 0,
  `saldo_nuevo` float NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_producto` (`id_producto`),
  KEY `idx_codigo` (`codigo_producto`),
  KEY `idx_fecha` (`fecha_movimiento`),
  KEY `idx_ubicacion` (`ubicacion`),
  FOREIGN KEY (`id_producto`) REFERENCES `c_productos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Vista para obtener el saldo actual por producto en vitrina
CREATE VIEW `v_saldo_vitrina` AS
SELECT
    p.id,
    p.codigo,
    p.descripcion,
    COALESCE(
        (SELECT
            SUM(CASE
                WHEN tipo_movimiento = 'ENTRADA' THEN cantidad
                WHEN tipo_movimiento = 'SALIDA' THEN -cantidad
                ELSE 0
            END)
         FROM movimiento_ubicacion mu
         WHERE mu.id_producto = p.id
         AND mu.ubicacion = 'Vitrina'
        ), 0
    ) as saldo_vitrina
FROM c_productos p
WHERE p.estado = 1;

-- Procedimiento para registrar movimiento en vitrina
DELIMITER //
CREATE PROCEDURE `sp_registrar_movimiento_vitrina`(
    IN p_id_producto INT,
    IN p_tipo_movimiento ENUM('ENTRADA','SALIDA'),
    IN p_cantidad FLOAT,
    IN p_usuario_id INT,
    IN p_observaciones TEXT
)
BEGIN
    DECLARE v_codigo VARCHAR(255);
    DECLARE v_descripcion VARCHAR(255);
    DECLARE v_saldo_anterior FLOAT DEFAULT 0;
    DECLARE v_saldo_nuevo FLOAT DEFAULT 0;

    -- Obtener datos del producto
    SELECT codigo, descripcion
    INTO v_codigo, v_descripcion
    FROM c_productos
    WHERE id = p_id_producto;

    -- Obtener saldo anterior
    SELECT COALESCE(saldo_vitrina, 0)
    INTO v_saldo_anterior
    FROM v_saldo_vitrina
    WHERE id = p_id_producto;

    -- Calcular nuevo saldo
    IF p_tipo_movimiento = 'ENTRADA' THEN
        SET v_saldo_nuevo = v_saldo_anterior + p_cantidad;
    ELSE
        SET v_saldo_nuevo = v_saldo_anterior - p_cantidad;
    END IF;

    -- Insertar movimiento
    INSERT INTO movimiento_ubicacion (
        id_producto,
        codigo_producto,
        descripcion_producto,
        tipo_movimiento,
        cantidad,
        ubicacion,
        usuario_id,
        observaciones,
        saldo_anterior,
        saldo_nuevo
    ) VALUES (
        p_id_producto,
        v_codigo,
        v_descripcion,
        p_tipo_movimiento,
        p_cantidad,
        'Vitrina',
        p_usuario_id,
        p_observaciones,
        v_saldo_anterior,
        v_saldo_nuevo
    );

END //
DELIMITER ;

-- Función para obtener saldo actual de un producto en vitrina
DELIMITER //
CREATE FUNCTION `fn_saldo_producto_vitrina`(p_id_producto INT)
RETURNS FLOAT
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE v_saldo FLOAT DEFAULT 0;

    SELECT COALESCE(saldo_vitrina, 0)
    INTO v_saldo
    FROM v_saldo_vitrina
    WHERE id = p_id_producto;

    RETURN v_saldo;
END //
DELIMITER ;