<?php

    require_once "../modelos/Mercadeo.php";

    (strlen(session_id()) < 1) ? session_start(): null;

    $mercadeo = new Mercadeo();

    $nombre = isset($_POST["nombreMercadeo"])? limpiarCadena($_POST["nombreMercadeo"]):"";
    $empresa = isset($_POST["empresaMercadeo"])? limpiarCadena($_POST["empresaMercadeo"]):"";
    $telefono = isset($_POST["telefonocMercadeo"])? limpiarCadena($_POST["telefonocMercadeo"]):"";
    $direccion = isset($_POST["direccioncMercadeo"])? limpiarCadena($_POST["direccioncMercadeo"]):"";
    $actividadComercial = isset($_POST["actividadComercial"])? $_POST["actividadComercial"] : null;
    $productosInteres = isset($_POST["productosInteres"])? $_POST["productosInteres"] : null;

    switch ($_GET['op']) {
        case 'crearClienteMercadeo':

            $actividades = implode(',', $actividadComercial);
            $productos = implode(',', $productosInteres);

            $rspta = $mercadeo->insertarMercadeo($nombre, $empresa, $telefono, $direccion, $actividades, $productos);

            echo $rspta ? "Cliente creado" : "Cliente no se pudo crear";
        break;
    }

?>