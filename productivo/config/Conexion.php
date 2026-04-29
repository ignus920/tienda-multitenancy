<?php 
require_once "global.php";

// Creamos la conexión usando MySQLi
$conexion = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Configuramos la codificación de caracteres
mysqli_query($conexion, 'SET NAMES "'.DB_ENCODE.'"');

// Verificamos si hubo error de conexión
if ($conexion->connect_errno) {
    printf("Falló conexión a la base de datos: %s\n", $conexion->connect_error);
    exit();
}

// Funciones auxiliares para ejecutar consultas
if (!function_exists('ejecutarConsulta')) {

    // Ejecuta una consulta que devuelve un conjunto de resultados
    function ejecutarConsulta($sql) {
        global $conexion;
        return $conexion->query($sql);
    }

    // Ejecuta una consulta y devuelve una sola fila
    function ejecutarConsultaSimpleFila($sql) {
        global $conexion;
        $query = $conexion->query($sql);
        return $query->fetch_assoc();
    }

    // Ejecuta una consulta de inserción y retorna el ID insertado
    function ejecutarConsulta_retornarID($sql) {
        global $conexion;
        $conexion->query($sql);
        return $conexion->insert_id;
    }

    // Limpia una cadena para evitar inyecciones SQL y XSS
    function limpiarCadena($str) {
        global $conexion;
        $str = mysqli_real_escape_string($conexion, trim($str));
        return htmlspecialchars($str);
    }

    // Limpia una cadena solo para MySQL, manteniendo el HTML
    function limpiarCadenaHTML($str) {
        global $conexion;
        return mysqli_real_escape_string($conexion, trim($str));
    }

    // Ejecuta una consulta y devuelve todas las filas en un array asociativo
    function ejecutarConsultaArray($sql) {
        global $conexion;
        $query = $conexion->query($sql);
        $result = array();
        while ($row = $query->fetch_assoc()) {
            $result[] = $row;
        }
        return $result;
    }


	 // Ejecuta un array de consultas SQL en una transacción
    function ejecutarTransaccion($sqlArray) {
        global $conexion;
        $conexion->begin_transaction(); // Inicia la transacción

        try {
            foreach ($sqlArray as $sql) {
                if (!$conexion->query($sql)) {
                    throw new Exception("Error en consulta: $sql");
                }
            }
            $conexion->commit(); // Si todas las consultas van bien, confirmar cambios
            return true;
        } catch (Exception $e) {
            $conexion->rollback(); // Si algo falla, deshacer todos los cambios
            error_log($e->getMessage());
            return false;
        }
    }

    // Verifica si existe al menos un registro que cumpla con la condición
    function existeRegistro($sql) {
        global $conexion;
        $query = $conexion->query($sql);
        return $query->num_rows > 0;
    }

    // Devuelve el número total de filas de una consulta
    function contarFilas($sql) {
        global $conexion;
        $query = $conexion->query($sql);
        return $query->num_rows;
    }
}
?>
