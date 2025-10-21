<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION["userid"])) {
    header("Location: ../index.php?error=Sin_sesion_iniciada");
    exit();
}

include "conexion.php";

$id_diligencia = intval($_POST["id_diligencia"] ?? 0);
$id_expediente = intval($_POST["id_expediente"] ?? 0);
$es_destinatario = isset($_POST["es_destinatario"]) ? boolval($_POST["es_destinatario"]) : false;

// ✅ Verificación estricta: el archivo solo puede ser subido por el destinatario
if ($id_diligencia <= 0 || !$es_destinatario) {
    die("Operación no permitida o datos inválidos.");
}

if (!empty($_FILES["pdf_file"]["name"])) {
    $nombre_archivo = basename($_FILES["pdf_file"]["name"]);
    $ext = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));

    if ($ext !== "pdf") {
        die("Solo se permiten archivos PDF.");
    }

    // ✅ Carpeta de destino (ajustada a tu estructura)
    $carpeta_destino = __DIR__ . "/../uploads/diligencias/";
    if (!file_exists($carpeta_destino)) {
        mkdir($carpeta_destino, 0777, true);
    }

    // Crear nombre único
    $nuevo_nombre = "diligencia_" . $id_diligencia . "_" . time() . ".pdf";
    $ruta_final = $carpeta_destino . $nuevo_nombre;
    $ruta_guardada = "uploads/diligencias/" . $nuevo_nombre; // accesible desde navegador

    // ✅ Mover archivo
    if (move_uploaded_file($_FILES["pdf_file"]["tmp_name"], $ruta_final)) {
        // Guardar en BD
        $sql = "UPDATE exhorto_diligencias SET pdf_path = ? WHERE id_diligencia = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("si", $ruta_guardada, $id_diligencia);
        $stmt->execute();
        $stmt->close();

        // ✅ Redirigir con notificación tipo toast
        header("Location: ../viewExpediente.php?id=$id_expediente&msg=pdf_ok");
        exit();
    } else {
        die("Error al mover el archivo. Verifica los permisos de la carpeta destino.");
    }
} else {
    die("No se seleccionó ningún archivo.");
}
?>
