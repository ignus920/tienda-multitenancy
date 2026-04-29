<?php
// upload.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetDir = "../files/solicitudesComercial/"; // Carpeta donde se guardarán las imágenes
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true); // Crea la carpeta si no existe
    }

    // Verifica si se recibió una imagen desde el portapapeles
    if (isset($_POST['image'])) {
        $imageData = $_POST['image'];
        $imageData = str_replace('data:image/png;base64,', '', $imageData); // Elimina el encabezado base64
        $imageData = str_replace(' ', '+', $imageData); // Corrige espacios en la cadena base64
        $imageData = base64_decode($imageData);

        // Genera un nombre único para la imagen
        $fileName = uniqid() . '.png';
        $targetFilePath = $targetDir . $fileName;

        // Guarda la imagen en el servidor
        if (file_put_contents($targetFilePath, $imageData)) {
            echo json_encode(['status' => 'success', 'url' => $targetFilePath]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al guardar la imagen']);
        }
    } elseif (isset($_FILES['file'])) {
        // Maneja imágenes subidas normalmente (archivo adjunto)
        if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $fileName = uniqid() . '_' . basename($_FILES['file']['name']);
            $targetFilePath = $targetDir . $fileName;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFilePath)) {
                echo json_encode(['status' => 'success', 'url' => $targetFilePath]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error al mover la imagen']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error en la carga del archivo']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Datos de imagen no válidos']);
    }
}
?>