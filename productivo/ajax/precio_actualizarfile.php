<?php 
require_once "../modelos/Actualizar_precio.php";

$actualizar=new Actualizar_precio();


$idlista=isset($_POST["idlista"])? limpiarCadena($_POST["idlista"]):"";

	// require_once "../modelos/Sincrud.php";
	// $empresa=new Sincrud();
	// $rspta=$empresa->empresa();
	// $firma=$rspta['r_social'];
	// //$firma='Ticsia';
    $idlista=$_GET['id'];
	$rspta=$actualizar->listarpreciodetalle($idlista);
	$salida = "";
	$contador=1;
	$salida .= "<table>";
	$salida .= "<thead> <th>#</th>
	<th>Codigo</th>
	<th>Precio1</th>
	<th>Precio2</th>
	<th>Precio3</th>
	<th>Precio4</th>
	<th>Precio5</th>
	<th>Precio6</th>
	<th>Precio7</th>
	<th>Precio8</th>
	<th>Precio9</th>
	<th>Precio10</th>
	<th>Precio11</th>
	<th>Precio12</th>
	<th>Precio13</th>
	<th>Precio14</th>
	<th>Precio15</th>
	<th>Precio16</th>
	<th>Precio17</th>
	<th>Precio18</th>
	<th>Precio19</th>
	<th>Precio20</th>
	<th>Precio21</th>
	<th>Precio22</th>
	<th>Precio23</th>
	<th>Precio24</th>
    <th>Precio25</th>
	<th>Precio26</th>
	<th>Precio27</th>
	<th>Precio28</th>
	<th>Precio29</th>
	<th>Precio30</th>
	</thead>";
	while ($r=$rspta->fetch_object()){
		$salida .= "<tr><td>".$contador++."</td>
		<td>".$r->codigo."</td>
		<td>".$r->precio1."</td>
		<td>".$r->precio2."</td>
		<td>".$r->precio3."</td>

		<td>0</td>
		<td>0</td>
		<td>0</td>
		<td>0</td>
		<td>0</td>
		<td>0</td>
		<td>0</td>
		<td>0</td>
		<td>0</td>
		<td>0</td>
		<td>0</td>
		<td>0</td>
		<td>0</td>
		<td>0</td>
		<td>0</td>
		<td>0</td>
		<td>0</td>
		<td>0</td>
		<td>0</td>
		<td>0</td>
		<td>0</td>
		<td>0</td>
		<td>0</td>
		<td>0</td>
		<td>0</td>
		<td>0</td>
		<td>0</td>
		

		</tr>";
	}
	$salida .= "</table>";
	header("Content-type: application/vnd.ms-excel charset=iso-8859-1");
	header("Content-Disposition: attachment; filename=precioActualizar".time().".xls");
	header("Pragma: no-cache");
	header("Expires: 0");
	echo $salida;
	
// $r->descripcion
?>