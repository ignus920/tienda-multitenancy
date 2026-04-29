<?php
if (strlen(session_id()) < 1) 
session_start();
require_once "../modelos/Lista_pedidos.php";

$Listap=new ListaP();


$id=isset($_POST["id"])? limpiarCadena($_POST["id"]):"";
// Obtener los datos del formulario
$productos = isset($_POST['productos']) ? $_POST['productos'] : [];
$fecha_estimada = isset($_POST['fecha_estimada']) ? $_POST['fecha_estimada'] : '';


$id_pedido = isset($_POST['id_pedido']) ? $_POST['id_pedido'] : '';
$fecha_llegada = isset($_POST['fecha_llegada']) ? $_POST['fecha_llegada'] : '';








switch ($_GET["op"]){



	case 'cotizaciones':
		$rspta = $Listap->cabeza();
	
		$output = ''; // Variable para almacenar el HTML generado
	
		while ($reg = $rspta->fetch_object()) {
			$output .= '<div class="card mb-3 shadow-sm">'; // Card para cada cotización
			$output .= '<div class="card-header bg-primary text-white">';
			$output .= '<h5 class="mb-0">Cotización: ' . $reg->consecutivo . ' - Fecha: ' . $reg->fecha . '</h5>';
			$output .= '</div>';
			$output .= '<div class="card-body">';
	
			// Obtener los detalles de la cotización
			$detalles = $Listap->detalle($reg->id);
			if ($detalles->num_rows > 0) {
				$output .= '<ul class="list-group list-group-flush">'; // Lista de productos
				while ($detalle = $detalles->fetch_object()) {
					$output .= '<li class="list-group-item d-flex justify-content-between align-items-center">';
					$output .= '<div>';
					$output .= '<input type="checkbox" id="producto-' . $detalle->producto_id . '" name="productos" value="' . $detalle->producto_id . '" class="mr-2">';
					$output .= '<label for="producto-' . $detalle->producto_id . '">';
					$output .= '<strong>' . $detalle->nombre_producto . '</strong>';
					$output .= '<br><small>Producto ID: ' . $detalle->producto_id . ' - Precio: $' . $detalle->precio . ' - Cantidad: ' . $detalle->cant . '</small>';
					$output .= '</label>';
					$output .= '</div>';
					$output .= '</li>';
				}
				$output .= '</ul>';
			} else {
				$output .= '<p class="text-muted">No hay productos disponibles para esta cotización.</p>';
			}
	
			$output .= '</div>'; // Cierre de card-body
			$output .= '</div>'; // Cierre de card
		}
	
		echo $output; // Devolver el HTML generado
		break;






		case 'crearPedido':
			
		
			// Validar que se hayan seleccionado productos y que la fecha no esté vacía
			if (empty($productos) || empty($fecha_estimada)) {
				echo json_encode(['status' => 'error', 'message' => 'Debe seleccionar al menos un producto y una fecha estimada.']);
				exit;
			}
		
			// Crear el pedido
			$rspta = $Listap->crearPedido($productos, $fecha_estimada);
		
			// Devolver la respuesta
			if ($rspta) {
				echo json_encode(['status' => 'success', 'message' => 'Pedido registrado correctamente.']);
			} else {
				echo json_encode(['status' => 'error', 'message' => 'No se pudo registrar el pedido.']);
			}
			break;



	




	
			case 'listarPedidos':
				$opcion = "";
				$rspta = $Listap->listarPedidos();
			
				$data = array();
				while ($reg = $rspta->fetch_object()) {
					// Verificar si la fecha de llegada está vacía o nula
					if (empty($reg->fecha_llegada)) {
						$fecha_llegada = '<input type="date" class="form-control input-sm" id="fecha_llegada_' . $reg->id_pedido . '" onchange="actualizarFechaLlegada(' . $reg->id_pedido . ')">';
					} else {
						$fecha_llegada = $reg->fecha_llegada;
					}
			
					// Botón de edición
					$opcion = '<button class="btn btn-success" data-toggle="modal" data-target="#modalQuotes" onclick="verDetallePedido(' . $reg->id_pedido . ')"><i class="fas fa-eye"></i></button>';
			
					// Determinar el color del estado
					$estado_html = '';
					if ($reg->estado === 'PENDIENTE') {
						$estado_html = '<span class="badge badge-success">PENDIENTE</span>';
					} elseif ($reg->estado === 'RECIBIDO') {
						$estado_html = '<span class="badge badge-warning">RECIBIDO</span>';
					}
			
					$data[] = array(
						"0" => 'Pedido n° ' . $reg->consecutivo,
						"1" => $reg->fecha_creacion,
						"2" => $reg->fecha_estimada_llegada,
						"3" => $fecha_llegada,
						"4" => $estado_html, // Mostrar el estado con el badge correspondiente
						"5" => $opcion
					);
				}
			
				$results = array(
					"sEcho" => 1,
					"iTotalRecords" => count($data),
					"iTotalDisplayRecords" => count($data),
					"aaData" => $data
				);
				echo json_encode($results);
				break;




				case 'detallePedido':
					$id_pedido = isset($_POST['id_pedido']) ? $_POST['id_pedido'] : '';
					
					if (empty($id_pedido)) {
						echo json_encode(['status' => 'error', 'message' => 'ID de pedido no especificado.']);
						exit;
					}
				
					// Obtener el detalle del pedido
					$rspta = $Listap->detallePedido($id_pedido);
				
					// Formatear los datos para devolverlos como JSON
					$data = [];
					while ($reg = $rspta->fetch_assoc()) {
						$data[] = [
							'id_detalle' => $reg['id_detalle'],
							'producto_id' => $reg['producto_id'],
							'nombre_producto' => $reg['codigo'] . ' - ' . $reg['descripcion'],
							'cantidad' => $reg['cantidad'],
							'precio_unitario' => $reg['precio_unitario'],
							'total' => $reg['cantidad'] * $reg['precio_unitario']
						];
					}
				
					echo json_encode(['status' => 'success', 'data' => $data]);
					break;




				
					case 'actualizarFechaLlegada':
			
		
						// Validar que se hayan seleccionado productos y que la fecha no esté vacía
						if (empty($id_pedido) || empty($fecha_llegada)) {
							echo json_encode(['status' => 'error', 'message' => 'Debe seleccionar al menos un producto y una fecha estimada.']);
							exit;
						}
					
						// Crear el pedido
						$rspta = $Listap->actualizarFechaLlegada($id_pedido ,$fecha_llegada);
					
						// Devolver la respuesta
						if ($rspta) {
							echo json_encode(['status' => 'success', 'message' => 'Pedido registrado correctamente.']);
						} else {
							echo json_encode(['status' => 'error', 'message' => 'No se pudo registrar el pedido.']);
						}
						break;




}
?>