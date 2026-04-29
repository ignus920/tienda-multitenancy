<?php
/**
 * BUSCAR CÓDIGO DEL PRODUCTO ID 23469
 */

// Conexión básica sin archivos externos
$servername = "localhost"; // Ajusta según tu servidor
$username = "root";        // Ajusta según tu configuración
$password = "";            // Ajusta según tu configuración
$dbname = "fervicom";      // Ajusta según tu base de datos

echo "<h1>🔍 Buscar Código del Producto</h1>";
echo "<style>
    body { font-family: Arial; margin: 20px; }
    .success { color: green; background: #e8f5e8; padding: 10px; margin: 10px 0; border-left: 4px solid green; }
    .error { color: red; background: #ffe8e8; padding: 10px; margin: 10px 0; border-left: 4px solid red; }
    .info { color: blue; background: #e8f0ff; padding: 10px; margin: 10px 0; border-left: 4px solid blue; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>";

$id_producto = $_GET['id'] ?? '23469';

echo "<div class='info'>Buscando información del producto ID: $id_producto</div>";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Buscar el producto
    $stmt = $conn->prepare("SELECT * FROM c_productos WHERE id = :id");
    $stmt->bindParam(':id', $id_producto);
    $stmt->execute();
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($producto) {
        echo "<div class='success'>✅ Producto encontrado en ERP</div>";
        echo "<table>";
        echo "<tr><th>Campo</th><th>Valor</th></tr>";
        echo "<tr><td>ID</td><td>" . $producto['id'] . "</td></tr>";
        echo "<tr><td>Código</td><td><strong>" . $producto['codigo'] . "</strong></td></tr>";
        echo "<tr><td>Descripción</td><td>" . $producto['descripcion'] . "</td></tr>";
        echo "<tr><td>Imagen actual</td><td>" . ($producto['tximagen'] ?: 'Sin imagen') . "</td></tr>";
        echo "</table>";

        $codigo_producto = $producto['codigo'];

        echo "<h2>🔍 Buscar este código en WordPress</h2>";

        // Buscar en WordPress por SKU
        $wp_url = 'https://www.fervicom.com';
        $wp_user = 'fervicom';
        $wp_password = 'KNRJ kYEm Bau2 KSG7 CEHJ AhqC';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $wp_url . '/wp-json/wc/v3/products?sku=' . urlencode($codigo_producto));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . base64_encode($wp_user . ':' . $wp_password),
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 200) {
            $productos_wp = json_decode($response, true);
            if (!empty($productos_wp)) {
                echo "<div class='success'>✅ Producto encontrado en WordPress</div>";
                foreach ($productos_wp as $prod_wp) {
                    echo "<table>";
                    echo "<tr><th>Campo WordPress</th><th>Valor</th></tr>";
                    echo "<tr><td>ID</td><td>" . $prod_wp['id'] . "</td></tr>";
                    echo "<tr><td>Nombre</td><td>" . $prod_wp['name'] . "</td></tr>";
                    echo "<tr><td>SKU</td><td><strong>" . ($prod_wp['sku'] ?: 'SIN SKU') . "</strong></td></tr>";
                    echo "<tr><td>Tipo</td><td>" . $prod_wp['type'] . "</td></tr>";
                    echo "<tr><td>Imágenes</td><td>" . count($prod_wp['images'] ?? []) . "</td></tr>";
                    echo "</table>";
                }

                echo "<h2>🚀 Test de asignación directa</h2>";
                echo "<p><a href='test_asignacion_directa.php?id_erp=$id_producto&id_wp=" . $productos_wp[0]['id'] . "' style='background: #007cba; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>Hacer test de asignación</a></p>";
            } else {
                echo "<div class='error'>❌ Producto NO encontrado en WordPress</div>";

                // Buscar por nombre
                echo "<h3>🔍 Buscar por nombre...</h3>";
                $descripcion = $producto['descripcion'];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $wp_url . '/wp-json/wc/v3/products?search=' . urlencode($descripcion));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Basic ' . base64_encode($wp_user . ':' . $wp_password),
                    'Content-Type: application/json'
                ]);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);

                $response = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($http_code == 200) {
                    $productos_nombre = json_decode($response, true);
                    if (!empty($productos_nombre)) {
                        echo "<div class='info'>📝 Productos similares encontrados por nombre:</div>";
                        foreach ($productos_nombre as $prod) {
                            echo "<p>• <strong>" . $prod['name'] . "</strong> (ID: " . $prod['id'] . ", SKU: " . ($prod['sku'] ?: 'Sin SKU') . ")</p>";
                        }
                    }
                }

                echo "<div class='info'>
                <h3>💡 Para solucionar:</h3>
                <ol>
                <li>Ve a WordPress → Productos</li>
                <li>Busca un producto con descripción similar: <strong>" . $descripcion . "</strong></li>
                <li>Asigna el SKU: <strong>" . $codigo_producto . "</strong></li>
                <li>O crea el producto en WordPress con ese SKU</li>
                </ol>
                </div>";
            }
        } else {
            echo "<div class='error'>❌ Error conectando con WordPress (HTTP $http_code)</div>";
        }

    } else {
        echo "<div class='error'>❌ Producto no encontrado en la base de datos ERP</div>";
    }

} catch(PDOException $e) {
    echo "<div class='error'>❌ Error de conexión: " . $e->getMessage() . "</div>";
    echo "<div class='info'>
    <h3>🔧 Configuración de base de datos</h3>
    <p>Edita este archivo y ajusta la configuración:</p>
    <ul>
    <li><strong>Servidor:</strong> $servername</li>
    <li><strong>Usuario:</strong> $username</li>
    <li><strong>Base de datos:</strong> $dbname</li>
    </ul>
    </div>";
}

echo "<hr>";
echo "<p><strong>URLs útiles:</strong></p>";
echo "<ul>";
echo "<li><a href='test_simple_wp.php'>Volver al test simple</a></li>";
echo "<li><a href='buscar_codigo_producto.php?id=OTRO_ID'>Buscar otro producto</a></li>";
echo "</ul>";
?>